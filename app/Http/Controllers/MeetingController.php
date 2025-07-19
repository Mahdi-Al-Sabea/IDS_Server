<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Agenda;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationNotificationMail;
use App\Mail\BookingConfirmationNotificationMail;
use App\Models\Notification;
use App\Http\Controllers\NotificationController;
use App\Mail\MeetingStatusChangeMail;
use App\Mail\MeetingUpdateNotificationMail;
use App\Mail\sendTaskAssignmentMail;
use App\Models\MinutesOfMeeting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class MeetingController extends Controller
{


    protected $notificationController;

    public function __construct(NotificationController $notificationController)
    {
        $this->notificationController = $notificationController;
    }

    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Auto-complete past meetings
        Meeting::where('startsAt', '<', Carbon::now()->toDateTimeString())
            ->where('endsAt', '<', Carbon::now()->toDateTimeString())
            ->where('status', '!=', 'completed') // Optional, to avoid redundant writes
            ->where('status', '!=', 'cancelled') // Optional, to avoid redundant writes
            ->update(['status' => 'completed']);

        $query = Meeting::with(['attendees', 'agendas', 'room']);

        // Optional filters
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('organizer_id')) {
            $query->where('organizer_id', $request->organizer_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Execute query
        $meetings = $query->get();

        return $this->sendResponse('Meeting list retrieved successfully.', $meetings);
    }


    public function indexByDate($date, $roomid)
    {
        Meeting::where('startsAt', '<', Carbon::now()->toDateTimeString())
            ->where('endsAt', '<', Carbon::now()->toDateTimeString())
            ->where('status', '!=', 'completed') // Optional, to avoid redundant writes
            ->where('status', '!=', 'cancelled') // Optional, to avoid redundant writes
            ->update(['status' => 'completed']);

        // Make sure the date is a valid format
        $parsedDate = \Carbon\Carbon::parse($date)->toDateString();

        $meetings = Meeting::whereDate('startsAt', $parsedDate)->where('room_id', $roomid)->where('status', '!=', 'cancelled')->where('status', '!=', 'completed')->get();
        return $this->sendResponse('Meetings retrieved successfully.', $meetings);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'startsAt' => 'required|date',
            'endsAt' => 'required|date|after_or_equal:startsAt',
            'attendees' => 'sometimes|array',
            'attendees.*' => 'exists:users,id',
            'agendas' => 'required|array|min:1',
            'agendas.*.description' => 'required|string|max:10000',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        // ✅ Check for overlapping meetings
        $conflict = Meeting::where('room_id', $request->room_id)
            ->where(function ($query) use ($request) {
                $query->where('startsAt', '<', $request->endsAt)
                    ->where('endsAt', '>', $request->startsAt)
                    ->where('status', '!=', 'cancelled');
            })
            ->exists();

        if ($conflict) {
            return $this->sendError('Meeting conflict', ['This room is already booked during that time.'], 409);
        }



        $organizerId = Auth::id();

        // Create the meeting
        $meeting = Meeting::create([
            'room_id' => $request->room_id,
            'organizer_id' => $organizerId,
            'title' => $request->title,
            'description' => $request->description,
            'startsAt' => $request->startsAt,
            'endsAt' => $request->endsAt,
            'status' => 'booked',
        ]);

        // Attach attendees
        $meeting->attendees()->sync($request->attendees ?? []);

        // Create agendas
        $meeting->agendas()->createMany($request->agendas);
        $meeting->minutes()->create([
            'decisions' => '',
            'discussedPoints' => '',
        ]);



        Mail::to(Auth::user()->email)->send(new BookingConfirmationNotificationMail($meeting, Auth::user()));
        $this->notificationController->store(
            'Meeting Confirmation',
            'Your meeting for room ' . $meeting->room->roomname . ' has been successfully booked.',
            Auth::user()->id
        );

        foreach ($meeting->attendees as $attendee) {
            // Send notification to each attendee
            Mail::to($attendee->email)->send(new InvitationNotificationMail($meeting, $attendee));
            $this->notificationController->store(
                'Meeting Invitation',
                'You have been invited to a meeting in room ' . $meeting->room->roomname . '.',
                $attendee->id
            );
        }

        return $this->sendResponse('Meeting created successfully.', $meeting->load(['attendees', 'agendas']), 201);
    }



    public function show(string $id)
    {
        $meeting = Meeting::with([
            'attendees',
            'agendas',
            'room',
            'organizer',
            'minutes.attachments.uploader',
            'minutes.actionItems.assignee',
        ])->find($id);

        if ($meeting->startsAt->lt(now()) && $meeting->endsAt->lt(now()) && $meeting->status != "cancelled" && $meeting != "completed") {
            $meeting->status = "completed";
            $meeting->save();
        }
        if (!$meeting) {
            return $this->sendError('Meeting not found.', [], 404);
        }

        return $this->sendResponse('Meeting retrieved successfully.', $meeting);
    }

    public function updateStatus(Request $request, string $id)
    {
        $meeting = Meeting::find($id);
        if (!$meeting) {
            return $this->sendError('Meeting not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:cancelled,completed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $meeting->status = $request->status;
        $meeting->save();

        // Notify attendees
        $notificationTitle = 'Meeting Status Update';
        $roomName = $meeting->room->roomname ?? 'the assigned room';
        $statusMessage = match ($request->status) {
            'cancelled' => 'has been cancelled.',
            'completed' => 'has been completed.',
            default => 'status has been updated.',
        };

        foreach ($meeting->attendees as $attendee) {
            Mail::to($attendee->email)->send(new MeetingStatusChangeMail($meeting, $attendee, $request->status));
            $this->notificationController->store(
                $notificationTitle,
                "A meeting you had in room $roomName $statusMessage",
                $attendee->id
            );
        }

        return $this->sendResponse("Meeting status updated to {$request->status}.", $meeting);
    }


    public function update(Request $request, string $id)
    {
        $meeting = Meeting::find($id);
        if (!$meeting) {
            return $this->sendError('Meeting not found.', [], 404);
        }



        if ($request->has('status') && $request->status === 'cancelled') {
            $meeting->status = 'cancelled';
            $meeting->save();

            foreach ($meeting->attendees as $attendee) {
                Mail::to($attendee->email)->send(new MeetingStatusChangeMail($meeting, $attendee, "cancelled"));
                $this->notificationController->store(
                    'Meeting Cancellation',
                    'A meeting you had in room ' . $meeting->room->roomname . ' has been cancelled.',
                    $attendee->id
                );
            }

            return $this->sendResponse('Meeting cancelled successfully.', $meeting);
        }

        if ($request->has('status') && $request->status === 'completed') {
            $meeting->status = 'completed';
            $meeting->save();

            foreach ($meeting->attendees as $attendee) {
                Mail::to($attendee->email)->send(new MeetingStatusChangeMail($meeting, $attendee, "completed"));
                $this->notificationController->store(
                    'Meeting Completion',
                    'A meeting you had in room ' . $meeting->room->roomname . ' has been completed.',
                    $attendee->id
                );
            }

            return $this->sendResponse('Meeting completed successfully.', $meeting);
        }

        if (
            $request->room_id != $meeting->room_id ||
            Carbon::parse($request->startsAt)->ne($meeting->startsAt) ||
            Carbon::parse($request->endsAt)->ne($meeting->endsAt)
        ) {
            $conflict = Meeting::where('room_id', $request->room_id)
                ->where(function ($query) use ($request, $id) {
                    $query->where('startsAt', '<', $request->endsAt)
                        ->where('endsAt', '>', $request->startsAt)
                        ->where('id', '!=', $id)
                        ->where('status', '!=', 'cancelled');
                })
                ->exists();

            if ($conflict) {
                return $this->sendError('Meeting conflict', ['This room is already booked during that time.'], 409);
            }
        }

        // Notify attendees if rescheduled
        if (Carbon::parse($request->startsAt) != Carbon::parse($meeting->startsAt) || Carbon::parse($request->endsAt) != Carbon::parse($meeting->endsAt)) {


            $request->merge(['status' => 'rescheduled']);
            foreach ($meeting->attendees as $attendee) {
                Mail::to($attendee->email)->send(new MeetingUpdateNotificationMail($meeting, $attendee));
                $this->notificationController->store(
                    'Meeting Update',
                    'A meeting you had in room ' . $meeting->room->roomname . ' has been rescheduled.',
                    $attendee->id
                );
            }
        }

        // Update meeting main fields
        $meeting->update($request->only(['room_id', 'title', 'description', 'startsAt', 'endsAt', 'status']));

        // Update attendees
        if ($request->has('attendees')) {
            $meeting->attendees()->sync($request->attendees); // Handles pivot fields
        }

        // Update agendas
        if ($request->has('agendas')) {
            $meeting->agendas()->delete();
            $meeting->agendas()->createMany($request->agendas);
        }

        // Update or create minutes and related action items and attachments
        if ($request->has('minutes')) {
            $minutesData = $request->minutes;

            $minutes = $meeting->minutes()->updateOrCreate(
                ['id' => $minutesData['id'] ?? null],
                [
                    'discussedPoints' => $minutesData['discussedPoints'] ?? null,
                    'decisions' => $minutesData['decisions'] ?? null,
                ]
            );

            if (isset($minutesData['action_items']) && count($minutesData['action_items']) > 0) {
                $existingIds = collect($minutesData['action_items'])
                    ->pluck('id')
                    ->filter()
                    ->toArray();

                $minutes->actionItems()->whereNotIn('id', $existingIds)->delete();

                foreach ($minutesData['action_items'] as $item) {
                    if (!empty($item['id'])) {
                        // Existing item - fetch it from DB
                        $existingItem = $minutes->actionItems()->find($item['id']);

                        // Detect if assignedTo is changed
                        $assignedChanged = $existingItem && $existingItem->assignedTo != $item['assignedTo'];

                        $updatedItem = $minutes->actionItems()->updateOrCreate(
                            ['id' => $item['id']],
                            [
                                'description' => $item['description'],
                                'status' => $item['status'],
                                'assignedTo' => $item['assignedTo'],
                                'due_date' => $item['dueDate'] ?? null,
                            ]
                        );

                        // Send notification if reassigned
                        if ($assignedChanged) {
                            $this->sendTaskAssignmentNotification($meeting, $updatedItem);
                        }
                    } else {
                        // New action item
                        $newItem = $minutes->actionItems()->create([
                            'description' => $item['description'],
                            'status' => $item['status'],
                            'assignedTo' => $item['assignedTo'],
                            'dueDate' => $item['dueDate'] ?? null,
                        ]);

                        $this->sendTaskAssignmentNotification($meeting, $newItem);
                    }
                }
            }



            // Attachments update
            if (isset($minutesData['attachments'])) {
                $existingAttachmentIds = collect($minutesData['attachments'])->pluck('id')->filter()->toArray();
                $minutes->attachments()->whereNotIn('id', $existingAttachmentIds)->delete();

                foreach ($minutesData['attachments'] as $attachmentData) {
                    $minutes->attachments()->updateOrCreate(
                        ['id' => $attachmentData['id'] ?? null],
                        [
                            'fileName' => $attachmentData['fileName'],
                            'filePath' => $attachmentData['filePath'],
                            'uploader_id' => $attachmentData['uploader_id'],
                        ]
                    );
                }
            }
        }

        return $this->sendResponse(
            'Meeting updated successfully.',
            $meeting->load(['attendees', 'agendas', 'minutes.attachments.uploader', 'minutes.actionItems.assignee'])
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $meeting = Meeting::find($id);
        if (!$meeting) {
            return $this->sendError('Meeting not found.', [], 404);
        }

        $organizerId = Auth::id();
        if ($organizerId !== $meeting->organizer_id) {
            return $this->sendError('Unauthorized', ['message' => 'You must be the organizer to delete this meeting.'], 401);
        }

        // Delete the meeting
        $meeting->delete();

        return $this->sendResponse('Meeting deleted successfully.', null, 204);
    }

    protected function sendTaskAssignmentNotification($meeting, $actionItem)
    {
        $assignee = User::find($actionItem->assignedTo);
        if (!$assignee) return;

        // Send email
        Mail::to($assignee->email)->send(new sendTaskAssignmentMail($meeting, $actionItem, $assignee));

        // Send in-app notification
        $this->notificationController->store(
            'New Task Assigned',
            "You have been assigned a new task in meeting '{$meeting->title}' scheduled in room '{$meeting->room->roomname}'.",
            $assignee->id
        );
    }
}

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
use App\Mail\MeetingUpdateNotificationMail;
use App\Models\MinutesOfMeeting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

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
        Meeting::where('startsAt', '<', now())
            ->where('endsAt', '<', now())
            ->update(['status' => 'completed']);

        // Make sure the date is a valid format
        $parsedDate = \Carbon\Carbon::parse($date)->toDateString();

        $meetings = Meeting::whereDate('startsAt', $parsedDate)->where('room_id', $roomid)->get();
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
                    ->where('endsAt', '>', $request->startsAt);
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

        if ($meeting->startsAt->lt(now()) && $meeting->endsAt->lt(now()) && $meeting->status != "cancelled" && $meeting !="completed") {
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
            Mail::to($attendee->email)->send(new MeetingUpdateNotificationMail($meeting, $attendee));
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

        /*$validator = Validator::make($request->all(), [
            'room_id' => 'sometimes|exists:rooms,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'startsAt' => 'sometimes|date',
            'endsAt' => 'sometimes|date|after_or_equal:startsAt',
            'status' => 'sometimes|in:cancelled,completed',
            'attendees' => 'sometimes|array',
            'attendees' => 'sometimes|array',
            'attendees.*.Attended' => 'nullable|boolean',
            'agendas' => 'sometimes|array|min:1',
            'agendas.*.description' => 'sometimes|string|max:10000',

            // Minutes validation
            'minutes' => 'sometimes|array',
            'minutes.id' => 'sometimes|exists:minutes_of_meetings,id',
            'minutes.discussedPoints' => 'nullable|string',
            'minutes.decisions' => 'nullable|string',

            // Action items validation
            'minutes.action_items' => 'sometimes|array',
            'minutes.action_items.*.id' => 'sometimes|exists:action_items,id',
            'minutes.action_items.*.description' => 'nullable|string',
            'minutes.action_items.*.status' => 'nullable|string',
            'minutes.action_items.*.assignee_id' => 'nullable|exists:users,id',

            // Attachments validation
            'minutes.attachments' => 'sometimes|array',
            'minutes.attachments.*.id' => 'sometimes|exists:attachments,id',
            'minutes.attachments.*.fileName' => 'nullable|string',
            'minutes.attachments.*.filePath' => 'nullable|string',
            'minutes.attachments.*.uploader_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }*/

        $organizerId = Auth::id();
        // Uncomment if you want to restrict updates to organizer only
        /*
        if ($organizerId !== $meeting->organizer_id) {
            return $this->sendError('Unauthorized', ['message' => 'You must be the organizer to update this meeting.'], 401);
        }
        */

        if ($request->has('status') && $request->status === 'cancelled') {
            $meeting->status = 'cancelled';
            $meeting->save();

            foreach ($meeting->attendees as $attendee) {
                Mail::to($attendee->email)->send(new MeetingUpdateNotificationMail($meeting, $attendee));
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
                Mail::to($attendee->email)->send(new MeetingUpdateNotificationMail($meeting, $attendee));
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
                        ->where('id', '!=', $id);
                })
                ->exists();

            if ($conflict) {
                return $this->sendError('Meeting conflict', ['This room is already booked during that time.'], 409);
            }
        }

        // Notify attendees if rescheduled
        if ($request->startsAt != $meeting->startsAt || $request->endsAt != $meeting->endsAt) {
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

            // Action items update
            if (isset($minutesData['action_items'])) {
                $existingActionItemIds = collect($minutesData['action_items'])->pluck('id')->filter()->toArray();
                $minutes->actionItems()->whereNotIn('id', $existingActionItemIds)->delete();

                foreach ($minutesData['action_items'] as $itemData) {
                    $minutes->actionItems()->updateOrCreate(
                        ['id' => $itemData['id'] ?? null],
                        [
                            'description' => $itemData['description'],
                            'status' => $itemData['status'],
                            'assignee_id' => $itemData['assignee_id'],
                        ]
                    );
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
}

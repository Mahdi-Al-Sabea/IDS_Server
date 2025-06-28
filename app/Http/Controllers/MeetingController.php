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

class MeetingController extends Controller
{


    protected $notificationController;

    public function __construct(NotificationController $notificationController) {
        $this->notificationController = $notificationController;
    }

    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Optionally filter by room_id, organizer_id, or status
        $query = Meeting::with(['attendees', 'agendas']);

        if ($request->has('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->has('organizer_id')) {
            $query->where('organizer_id', $request->organizer_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Get the meetings
        $meetings = $query->get();

        return $this->sendResponse('Meeting list retrieved successfully.', $meetings);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'room_id' => 'required|exists:rooms,id',
        /* 'organizer_id' => 'required|exists:users,id', */
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


        if (!$meeting) {
            return $this->sendError('Meeting not found.', [], 404);
        }

        return $this->sendResponse('Meeting retrieved successfully.', $meeting);
    }


    public function update(Request $request, string $id)
    {
        $meeting = Meeting::find($id);
        if (!$meeting) {
            return $this->sendError('Meeting not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
        'room_id' => 'required|exists:rooms,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'startsAt' => 'required|date',
        'endsAt' => 'required|date|after_or_equal:startsAt',
        'status' => 'sometimes|in:cancelled',
        'attendees' => 'sometimes|array',
        'attendees.*' => 'exists:users,id',
        'agendas' => 'required|array|min:1',
        'agendas.*.description' => 'required|string|max:10000',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $organizerId = Auth::id();
        /*if ($organizerId !== $meeting->organizer_id) {
            return $this->sendError('Unauthorized', ['message' => 'You must be the organizer to update this meeting.'], 401);
        }*/

        if ($request->has('status') && $request->status === 'cancelled') { 
            // If the meeting is being cancelled, we can skip the conflict check
            $meeting->status = 'cancelled';
            $meeting->endsAt = now(); // Set the end time to now or any other logic you prefer
            $meeting->startsAt = now(); // Set the start time to now or any other logic you prefer
            $meeting->save();


            foreach ($meeting->attendees as $attendee) {
                // Send notification to each attendee
                Mail::to($attendee->email)->send(new MeetingUpdateNotificationMail($meeting, $attendee));
                $this->notificationController->store(
                    'Meeting Cancellation',
                    'A meeting you had in room ' . $meeting->room->roomname . ' has been cancelled.',
                    $attendee->id
                );
            }

            return $this->sendResponse('Meeting cancelled successfully.', $meeting);
        }         

            // ✅ Check for overlapping meetings
        $conflict = Meeting::where('room_id', $request->room_id)
        ->where(function ($query) use ($request, $id) {
            $query->where('startsAt', '<', $request->endsAt)
                ->where('endsAt', '>', $request->startsAt)
                ->where('id', '!=', $id); // Exclude the current meeting
        })
        ->exists();

        if ($conflict) {
            return $this->sendError('Meeting conflict', ['This room is already booked during that time.'], 409);
        }

        if($request->startsAt !=$meeting->startsAt || $request->endsAt != $meeting->endsAt) {
            $request->merge(['status' => 'rescheduled']);
            // If the start or end time has changed, we need to notify attendees
            foreach ($meeting->attendees as $attendee) {
                 // Set status to updated for notification
                // Send notification to each attendee
                Mail::to($attendee->email)->send(new MeetingUpdateNotificationMail($meeting, $attendee));
                $this->notificationController->store(
                    'Meeting Update',
                    'A meeting you had in room ' . $meeting->room->roomname . ' has been rescheduled.',
                    $attendee->id
                );
            }
        }

        // Update the meeting
        $meeting->update($request->only(['room_id', 'title', 'description', 'startsAt', 'endsAt', 'status']));

        // Update attendees
        if ($request->has('attendees')) {
            $meeting->attendees()->sync($request->attendees);
        }

        // Update agendas
        if ($request->has('agendas')) {
            $meeting->agendas()->delete();
            $meeting->agendas()->createMany($request->agendas);
        }

        return $this->sendResponse('Meeting updated successfully.', $meeting->load(['attendees', 'agendas']));
        
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

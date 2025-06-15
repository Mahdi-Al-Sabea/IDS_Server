<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Agenda;
use App\Models\User;

class MeetingController extends Controller
{
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
        'status' => 'required|in:booked,rescheduled,cancelled',
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
        'status' => $request->status,
    ]);

    // Attach attendees
    $meeting->attendees()->sync($request->attendees ?? []);

    // Create agendas
    $meeting->agendas()->createMany($request->agendas);

    return $this->sendResponse('Meeting created successfully.', $meeting->load(['attendees', 'agendas']), 201);
}



    public function show(string $id)
    {
        $meeting = Meeting::with(['attendees', 'agendas'])->find($id);
        if (!$meeting) {
            return $this->sendError('Meeting not found.', [], 404);
        }
        return $this->sendResponse('Meeting retrieved successfully.', $meeting);
        
    }

    /**
     * Update the specified resource in storage.
     */
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
        'status' => 'required|in:booked,rescheduled,cancelled',
        'attendees' => 'sometimes|array',
        'attendees.*' => 'exists:users,id',
        'agendas' => 'required|array|min:1',
        'agendas.*.description' => 'required|string|max:10000',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $organizerId = Auth::id();
        if ($organizerId !== $meeting->organizer_id) {
            return $this->sendError('Unauthorized', ['message' => 'You must be the organizer to update this meeting.'], 401);
        }

         if ($request->has('status') && $request->status === 'cancelled') { 
            // If the meeting is being cancelled, we can skip the conflict check
            $meeting->status = 'cancelled';
            $meeting->endsAt = now(); // Set the end time to now or any other logic you prefer
            $meeting->startsAt = now(); // Set the start time to now or any other logic you prefer
            $meeting->save();
            return $this->sendResponse('Meeting cancelled successfully.', $meeting);
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

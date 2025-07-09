<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use Illuminate\Support\Facades\Validator;
use App\Models\Feature;
use App\Models\Meeting;
use App\Traits\ApiResponse; // Assuming you have this trait for API responses
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Room::with('features');

        if ($request->has('floor') && $request->floor !== null) {
            $query->where('floor', $request->floor);
        }
        if ($request->has('minCapacity') && $request->minCapacity !== null) {
            $query->where('capacity', '>=', $request->minCapacity);
        }
        if ($request->has('maxCapacity') && $request->maxCapacity !== null) {
            $query->where('capacity', '<=', $request->maxCapacity);
        }
        if ($request->has('roomname')) {
            $query->where('roomname', 'like', '%' . $request->roomname . '%');
        }
        $perPage = $request->input('per_page', 5); // Default to 5 rooms per page
        $rooms = $query->paginate($perPage);
        return $this->sendResponse('Room list retrieved successfully.', $rooms);
    }

    public function indexNotPaginated(Request $request)
    {
        $query = Room::with('features');

        if ($request->has('floor') && $request->floor !== null) {
            $query->where('floor', $request->floor);
        }
        if ($request->has('minCapacity') && $request->minCapacity !== null) {
            $query->where('capacity', '>=', $request->minCapacity);
        }
        if ($request->has('maxCapacity') && $request->maxCapacity !== null) {
            $query->where('capacity', '<=', $request->maxCapacity);
        }
        if ($request->has('roomname')) {
            $query->where('roomname', 'like', '%' . $request->roomname . '%');
        }
        $query = Room::with('features');
        $rooms = $query->get();
        return $this->sendResponse('Room list retrieved successfully.', $rooms);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'roomname' => 'required|string|unique:rooms,roomname|max:255',
            'floor' => 'required|integer|min:-10|max:10', // Assuming floors are numbered from 1 to 10
            'capacity' => 'required|integer|min:10|max:1000',
            'position' => [
                'required',
                'integer',
                'between:1,6',
                Rule::unique('rooms')->where(function ($query) use ($request) {
                    return $query->where('floor', $request->floor);
                }),
            ],
            'features' => 'array', // Optional
            'features.*' => 'exists:features,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $room = Room::create($request->only(['roomname', 'floor', 'capacity', 'position']));

        // Attach features if provided
        if ($request->has('features')) {
            $room->features()->attach($request->features);
        }

        return $this->sendResponse('Room created successfully', $room->load('features'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $room = Room::with('features')->find($id);
        if (!$room) {
            return $this->sendError('Room not found.', [], 404);
        }
        return $this->sendResponse('Room retrieved successfully.', $room);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $room = Room::findOrFail($id);



        $validator = Validator::make($request->all(), [
            'roomname' => 'sometimes|string',
            'floor' => 'sometimes|integer|min:-10|max:10', // Assuming floors are numbered from -10 to 10
            'capacity' => 'sometimes|integer|min:10|max:1000',
            'position' => [
                'required',
                'integer',
                'between:1,6',
                Rule::unique('rooms')->where(function ($query) use ($request) {
                    return $query->where('floor', $request->floor);
                })->ignore($room?->id), // ignore current room ID on update
            ],
            'features' => 'array',
            'features.*' => 'exists:features,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $room->update($request->only(['roomname', 'floor', 'capacity', 'position']));

        if ($request->has('features')) {
            // Sync replaces the existing features with new ones
            $room->features()->sync($request->features);
        }

        return $this->sendResponse('Room updated successfully', $room->load('features'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $room = Room::find($id);
        if (!$room) {
            return $this->sendError('Room not found.', [], 404);
        }

        // Detach features before deleting the room
        $room->features()->detach();

        $room->delete();
        return $this->sendResponse('Room deleted successfully.', null, 204);
    }



    public function getAvailableRoomsForNextHour()
    {
        $nowLebanon = Carbon::now('Asia/Beirut');

        $oneHourLaterUtc = $nowLebanon->copy()->addHour();
        $now = Carbon::now();
        $oneHourLater = $now->copy()->addHour();

        $occupiedRoomIds = Meeting::where(function ($query) use ($now, $oneHourLater) {
            $query->whereBetween('startsAt', [$now, $oneHourLater])
                ->orWhereBetween('endsAt', [$now, $oneHourLater])
                ->orWhere(function ($q) use ($now, $oneHourLater) {
                    $q->where('startsAt', '<=', $now)
                        ->where('endsAt', '>=', $oneHourLater);
                });
        })->pluck('room_id')->unique();

        $availableRooms = Room::whereNotIn('id', $occupiedRoomIds)->get();

        return response()->json([
            'success' => true,
            'message' => 'Available rooms retrieved successfully.',
            'nowLebanon' => $nowLebanon->toDateTimeString(),
            'oneHourLaterUtc' => $oneHourLaterUtc->toDateTimeString(),
            'occupiedRoomIds' => $occupiedRoomIds,
            'available_rooms_count' => $availableRooms->count(),
            'available_rooms' => $availableRooms,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Available rooms retrieved successfully.',
            'available_rooms_count' => $availableRooms->count(),
            'available_rooms' => $availableRooms,
        ]);
    }
}

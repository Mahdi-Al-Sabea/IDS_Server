<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {


        $userId = $request->user()->id; // Get the authenticated user's ID
        $userRole = $request->user()->role; // Get the authenticated user's role

       if($request->has('per_page')) {
        $perPage = $request->input('per_page', 5); // Default to 5 notifications per page
        $notifications = Notification::where('receiver_id', $userId)->paginate($perPage);
        } else {
            $notifications = Notification::where('receiver_id', $userId)->get(); // Get all notifications for the user
        }


        if ($notifications->isEmpty()) {
            return $this->sendError('No notifications found for this user.', null, 404);
        }
        return $this->sendResponse('Notifications retrieved successfully.', $notifications);
    } 


    /**
     * Store a newly created resource in storage.
     */
    public function store($subject,$content, $user_id)
    {
        $request = new Request([
            'subject' => $subject,
            'content' => $content,
            'receiver_id' => $user_id
        ]);

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'receiver_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }
    
        $notification = Notification::create($request->all());



        return $this->sendResponse('Notification created successfully.', $notification, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return $this->sendError('Notification not found', null, 404);
        }
        return $this->sendResponse('Notification retrieved successfully.', $notification);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return $this->sendError('Notification not found', null, 404);
        }
        $notification->update($request->all());
        return $this->sendResponse('Notification updated successfully.', $notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return $this->sendError('Notification not found', null, 404);
        }
        $notification->delete();
        return $this->sendResponse('Notification deleted successfully.', null, 204);
    }


/*     public function showByUserId(Request $request,$userId)
    {
        //$notifications = Notification::where('receiver_id', $userId)->get();
        $perPage = $request->input('per_page', 5); // Default to 5 notifications per page
        $notifications = Notification::where('receiver_id', $userId)->paginate($perPage);

        if ($notifications->isEmpty()) {
            return $this->sendError('No notifications found for this user.', null, 404);
        }
        return $this->sendResponse('Notifications retrieved successfully.', $notifications);
    } */

}

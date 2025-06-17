<?php

namespace App\Http\Controllers;

use App\Models\ActionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class ActionItemController extends Controller
{
    use ApiResponse;


    public function index()
    {
        $items = ActionItem::with('assignedUser')->get();
        return $this->sendResponse('Action items retrieved successfully.', $items);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'status' => 'required|in:Completed,Pending',
            'dueDate' => 'required|date',
            'assignedTo' => 'required|exists:users,id',
            'minutes_of_meeting_id' => 'required|exists:minutes_of_meetings,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors());
        }

        $actionItem = ActionItem::create($request->all());
        return $this->sendResponse('Action item created successfully.', $actionItem, 201);
    }


    public function show($id)
    {
        $item = ActionItem::find($id);

        if (!$item) {
            return $this->sendError('Action item not found.', [], 404);
        }

        return $this->sendResponse('Action item retrieved successfully.', $item);
    }

    public function update(Request $request, $id)
    {
        $item = ActionItem::find($id);

        if (!$item) {
            return $this->sendError('Action item not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:Completed,Pending',
            'dueDate' => 'sometimes|required|date',
            'assignedTo' => 'sometimes|required|exists:users,id',
            'minutes_of_meeting_id' => 'sometimes|required|exists:minutes_of_meetings,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors());
        }

        $item->update($request->all());
        return $this->sendResponse('Action item updated successfully.', $item);
    }

    public function destroy($id)
    {
        $item = ActionItem::find($id);

        if (!$item) {
            return $this->sendError('Action item not found.', [], 404);
        }

        $item->delete();
        return $this->sendResponse('Action item deleted successfully.', null, 204);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\MinutesOfMeeting;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class MinutesOfMeetingController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $minutes = MinutesOfMeeting::select('id', 'meeting_id', 'decisions', 'discussedPoints')->get();
        return $this->sendResponse('Minutes of meetings retrieved successfully.', $minutes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required|exists:meetings,id',
            'decisions' => 'nullable|string',
            'discussedPoints' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $meeting = Meeting::find($request->meeting_id);

        /*if (Auth::id() !== $meeting->organizer_id) {
            return $this->sendError('Unauthorized', ['message' => 'Only the meeting organizer can create minutes.'], 401);
        }*/

        $minutes = MinutesOfMeeting::create($request->only(['meeting_id', 'decisions', 'discussedPoints']));

        return $this->sendResponse('Minutes of meeting created successfully.', $minutes, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $minutes = MinutesOfMeeting::select('id', 'meeting_id', 'decisions', 'discussedPoints')->find($id);

        if (!$minutes) {
            return $this->sendError('Minutes of meeting not found.', [], 404);
        }

        return $this->sendResponse('Minutes of meeting retrieved successfully.', $minutes);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $minutes = MinutesOfMeeting::find($id);

        if (!$minutes) {
            return $this->sendError('Minutes of meeting not found.', [], 404);
        }

        $meeting = Meeting::find($minutes->meeting_id);

        /*if (Auth::id() !== $meeting->organizer_id) {
            return $this->sendError('Unauthorized', ['message' => 'Only the meeting organizer can update minutes.'], 401);
        }*/

        $validator = Validator::make($request->all(), [
            'decisions' => 'nullable|string',
            'discussedPoints' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $minutes->update($request->only(['decisions', 'discussedPoints']));

        return $this->sendResponse('Minutes of meeting updated successfully.', $minutes);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $minutes = MinutesOfMeeting::find($id);

        if (!$minutes) {
            return $this->sendError('Minutes of meeting not found.', [], 404);
        }

        $meeting = Meeting::find($minutes->meeting_id);

        if (Auth::id() !== $meeting->organizer_id) {
            return $this->sendError('Unauthorized', ['message' => 'Only the meeting organizer can delete minutes.'], 401);
        }

        $minutes->delete();

        return $this->sendResponse('Minutes of meeting deleted successfully.', null, 204);
    }


    public function generateReport($id)
    {


    $minutes = MinutesOfMeeting::with(['meeting', 'attachments', 'actionItems'])->find($id);

        $pdf = Pdf::loadView('pdf.report', ['minutes' => $minutes]);
    return response($pdf->output(), 200)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="report.pdf"');
    }
}

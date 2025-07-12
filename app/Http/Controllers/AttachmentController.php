<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttachmentController extends Controller
{
    use ApiResponse;


    public function index()
    {
        $attachments = Attachment::all();
        return $this->sendResponse('Attachment list retrieved successfully.', $attachments);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,xlsx,xls|max:20480',
            'minutes_of_meeting_id' => 'required|exists:minutes_of_meetings,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $fileData = [];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $newFileName = time() . '-' . $file->getClientOriginalName();
            $file->storeAs('attachments', $newFileName, 'public');

            $fileData['fileName'] = $file->getClientOriginalName();
            $fileData['filePath'] = 'storage/attachments/' . $newFileName;
        }

        $fileData['uploadedBy'] = Auth::id();
        $fileData['minutes_of_meeting_id'] = $request->minutes_of_meeting_id;

        $attachment = Attachment::create($fileData);

        return $this->sendResponse('Attachment uploaded successfully.', $attachment, 201);
    }

    public function storeBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png,xlsx,xls|max:20480',
            'minutes_of_meeting_id' => 'required|exists:minutes_of_meetings,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $uploadedAttachments = [];

        foreach ($request->file('files') as $file) {
            $newFileName = time() . '-' . $file->getClientOriginalName();
            $file->storeAs('attachments', $newFileName, 'public');

            $attachment = Attachment::create([
                'fileName' => $file->getClientOriginalName(),
                'filePath' => 'storage/attachments/' . $newFileName,
                'uploadedBy' => Auth::id(),
                'minutes_of_meeting_id' => $request->minutes_of_meeting_id,
            ]);

            $uploadedAttachments[] = $attachment;
        }

        return $this->sendResponse('Attachments uploaded successfully.', $uploadedAttachments, 201);
    }



    public function show($id)
    {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            return $this->sendError('Attachment not found.', [], 404);
        }

        return $this->sendResponse('Attachment retrieved successfully.', $attachment);
    }


    public function destroy($id)
    {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            return $this->sendError('Attachment not found.', [], 404);
        }

        $attachment->delete();

        return $this->sendResponse('Attachment deleted successfully.', null, 204);
    }
}

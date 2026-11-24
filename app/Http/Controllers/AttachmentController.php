<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Models\MedicalRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function store(StoreAttachmentRequest $request, MedicalRecord $medicalRecord): JsonResponse
    {
        $this->authorize('update', $medicalRecord);

        $file = $request->file('file');
        $path = $file->store("medical-records/{$medicalRecord->id}", 'private');

        $attachment = $medicalRecord->attachments()->create([
            'attachable_type' => MedicalRecord::class,
            'attachable_id'   => $medicalRecord->id,
            'file_path'       => $path,
            'file_name'       => $file->getClientOriginalName(),
            'mime_type'       => $file->getMimeType(),
        ]);

        return response()->json([
            'id'        => $attachment->id,
            'file_name' => $attachment->file_name,
            'mime_type' => $attachment->mime_type,
        ], 201);
    }

    public function destroy(MedicalRecord $medicalRecord, Attachment $attachment): JsonResponse
    {
        $this->authorize('update', $medicalRecord);

        if ($attachment->attachable_id !== $medicalRecord->id
            || $attachment->attachable_type !== MedicalRecord::class) {
            return response()->json(['message' => 'المرفق غير موجود.'], 404);
        }

        Storage::disk('private')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json(null, 204);
    }
}

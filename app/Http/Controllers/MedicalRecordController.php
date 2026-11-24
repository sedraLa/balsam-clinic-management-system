<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalRecordRequest;
use App\Http\Requests\UpdateMedicalRecordRequest;
use App\Http\Resources\MedicalRecordResource;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Illuminate\Http\JsonResponse;

class MedicalRecordController extends Controller
{
    public function show(Appointment $appointment): JsonResponse
    {
        $record = $appointment->medicalRecord;

        if (!$record) {
            return response()->json(['message' => 'لا يوجد سجل طبي لهذا الموعد.'], 404);
        }

        $this->authorize('view', $record);

        $record->load(['appointment', 'attachments']);

        return response()->json(new MedicalRecordResource($record));
    }

    public function store(StoreMedicalRecordRequest $request, Appointment $appointment): JsonResponse
    {
        if ($appointment->medicalRecord) {
            return response()->json(['message' => 'يوجد سجل طبي لهذا الموعد بالفعل.'], 422);
        }

        $record = new MedicalRecord($request->validated());
        $record->appointment_id = $appointment->id;

        $this->authorize('create', $record);

        $record->save();

        return response()->json(new MedicalRecordResource($record), 201);
    }

    public function update(UpdateMedicalRecordRequest $request, Appointment $appointment): JsonResponse
    {
        $record = $appointment->medicalRecord;

        if (!$record) {
            return response()->json(['message' => 'لا يوجد سجل طبي لهذا الموعد.'], 404);
        }

        $this->authorize('update', $record);

        $record->update($request->validated());

        $record->load(['appointment', 'attachments']);

        return response()->json(new MedicalRecordResource($record));
    }
}

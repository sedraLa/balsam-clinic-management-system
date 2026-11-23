<?php

namespace App\Http\Controllers;

use App\Exceptions\BookingException;
use App\Http\Requests\BookAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentBookingService;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentBookingService $bookingService) {}

    public function store(BookAppointmentRequest $request): JsonResponse
    {
        try {
            $appointment = $this->bookingService->book(
                $request->validated(),
                $request->user()
            );
        } catch (BookingException $e) {
            $status = $e->getCode() >= 400 ? $e->getCode() : 422;
            return response()->json(['message' => $e->getMessage()], $status);
        }

        $appointment->load(['doctor.user', 'doctor.specialty', 'patient', 'branch']);

        return response()->json(new AppointmentResource($appointment), 201);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        $this->authorize('view', $appointment);

        $appointment->load(['doctor.user', 'doctor.specialty', 'patient', 'branch']);

        return response()->json(new AppointmentResource($appointment));
    }

    public function cancel(Appointment $appointment): JsonResponse
    {
        $this->authorize('cancel', $appointment);

        try {
            $appointment = $this->bookingService->cancel($appointment, request()->user());
        } catch (BookingException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $appointment->load(['doctor.user', 'doctor.specialty', 'patient', 'branch']);

        return response()->json(new AppointmentResource($appointment));
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Exceptions\BookingException;
use App\Http\Requests\BookAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentBookingService $bookingService) {}

    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Appointment::with(['doctor.user', 'doctor.specialty', 'patient', 'branch']);

        match ($user->role) {
            UserRole::Admin        => null,
            UserRole::Receptionist => $query->forBranch($user->branch_id),
            UserRole::Doctor       => $query->forDoctor($user->doctor->id),
            UserRole::Patient      => $query->forPatient($user->patient->id),
        };

        if ($request->filled('status')) {
            $status = AppointmentStatus::tryFrom($request->status);
            if ($status) {
                $query->withStatus($status);
            }
        }

        if ($request->filled('date')) {
            $query->onDate($request->date);
        }

        if ($request->filled('branch_id') && $user->role === UserRole::Admin) {
            $query->forBranch((int) $request->branch_id);
        }

        if ($request->filled('doctor_id') && in_array($user->role, [UserRole::Admin, UserRole::Receptionist])) {
            $query->forDoctor((int) $request->doctor_id);
        }

        return response()->json(
            AppointmentResource::collection($query->orderByDesc('start_at')->paginate(20))
                ->response()->getData(true)
        );
    }

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

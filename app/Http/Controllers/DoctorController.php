<?php

namespace App\Http\Controllers;

use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id'    => ['nullable', 'integer', 'exists:branches,id'],
            'specialty_id' => ['nullable', 'integer', 'exists:specialties,id'],
        ]);

        $branchId    = $request->query('branch_id');
        $specialtyId = $request->query('specialty_id');
        $page        = $request->query('page', 1);

        $version  = Cache::get('doctors:list:version', 0);
        $cacheKey = "doctors:list:v{$version}:branch={$branchId}:specialty={$specialtyId}:page={$page}";

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($branchId, $specialtyId) {
            $query = Doctor::with(['user:id,name', 'specialty', 'schedules']);

            if ($branchId) {
                $query->whereHas('schedules', fn($q) =>
                    $q->where('branch_id', $branchId)->where('is_active', true)
                );
            }

            if ($specialtyId) {
                $query->where('specialty_id', $specialtyId);
            }

            return $query->paginate(15);
        });

        return response()->json(DoctorResource::collection($data)->response()->getData(true));
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'reference' => $this->reference,
            'status'    => $this->status->value,
            'start_at'  => $this->start_at?->format('Y-m-d H:i:s'),
            'end_at'    => $this->end_at?->format('Y-m-d H:i:s'),
            'doctor'    => $this->whenLoaded('doctor', fn() => [
                'id'        => $this->doctor->id,
                'name'      => $this->doctor->user?->name,
                'specialty' => $this->doctor->specialty?->name,
            ]),
            'patient'   => $this->whenLoaded('patient', fn() => [
                'id'        => $this->patient->id,
                'full_name' => $this->patient->full_name,
                'phone'     => $this->patient->phone,
            ]),
            'branch'    => $this->whenLoaded('branch', fn() => [
                'id'   => $this->branch->id,
                'name' => $this->branch->name,
                'code' => $this->branch->code,
            ]),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

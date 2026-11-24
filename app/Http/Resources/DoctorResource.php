<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->user?->name,
            'bio'       => $this->bio,
            'specialty' => $this->whenLoaded('specialty', fn() => [
                'id'   => $this->specialty->id,
                'name' => $this->specialty->name,
            ]),
            'branches'  => $this->whenLoaded('schedules', fn() =>
                $this->schedules
                    ->where('is_active', true)
                    ->pluck('branch_id')
                    ->unique()
                    ->values()
            ),
        ];
    }
}

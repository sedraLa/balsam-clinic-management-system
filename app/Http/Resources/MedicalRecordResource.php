<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'diagnosis'    => $this->diagnosis,
            'notes'        => $this->notes,
            'prescription' => $this->prescription,
            'appointment'  => $this->whenLoaded('appointment', fn() => [
                'id'        => $this->appointment->id,
                'reference' => $this->appointment->reference,
            ]),
            'attachments'  => $this->whenLoaded('attachments', fn() =>
                $this->attachments->map(fn($a) => [
                    'id'        => $a->id,
                    'file_name' => $a->file_name,
                    'mime_type' => $a->mime_type,
                ])
            ),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

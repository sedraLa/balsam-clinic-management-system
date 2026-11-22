<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\MedicalRecord;

class MedicalRecordObserver
{
    public function created(MedicalRecord $record): void
    {
        $this->log('created', $record, null, $record->getAttributes());
    }

    public function updated(MedicalRecord $record): void
    {
        $changed = $record->getChanges();
        unset($changed['updated_at']);

        if (empty($changed)) {
            return;
        }

        $old = array_intersect_key($record->getOriginal(), $changed);

        $this->log('updated', $record, $old, $changed);
    }

    public function deleted(MedicalRecord $record): void
    {
        $this->log('deleted', $record, $record->getOriginal(), null);
    }

    private function log(string $event, MedicalRecord $record, ?array $old, ?array $new): void
    {
        $user = auth()->user();

        ActivityLog::create([
            'subject_type' => MedicalRecord::class,
            'subject_id'   => $record->id,
            'causer_type'  => $user ? get_class($user) : null,
            'causer_id'    => $user?->id,
            'event'        => $event,
            'old_values'   => $old ?: null,
            'new_values'   => $new ?: null,
        ]);
    }
}

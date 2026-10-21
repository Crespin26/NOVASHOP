<?php

namespace Database\Factories;

use App\Models\AuditEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AuditEntry> */
class AuditEntryFactory extends Factory
{
    protected $model = AuditEntry::class;

    public function definition(): array
    {
        return ['action' => 'test', 'subject_type' => 'product', 'subject_id' => '1', 'metadata' => []];
    }
}

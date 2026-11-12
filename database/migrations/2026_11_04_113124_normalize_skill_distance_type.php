<?php

declare(strict_types=1);

use App\Models\Skill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Normalize skill.distance_type per Slice A Step A3:
 * store NULL for any distance_type that is not a single integer 1–4.
 * Ranges (distance_type<=N, >=N), composite tokens, and multi-token rows all map to NULL.
 *
 * This migration identifies rows with more than one exact `distance_type==N` token
 * in their condition_groups and sets distance_type to NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        $skills = Skill::whereNotNull('distance_type')->get(['id', 'export_id', 'name', 'condition_groups', 'distance_type']);

        $updated = 0;

        foreach ($skills as $skill) {
            $groups = $skill->condition_groups;

            if (! is_array($groups)) {
                continue;
            }

            $exactCount = 0;

            foreach ($groups as $group) {
                if (! is_array($group)) {
                    continue;
                }

                foreach ([$group['condition'] ?? null, $group['precondition'] ?? null] as $predicate) {
                    if (! is_string($predicate)) {
                        continue;
                    }

                    foreach (preg_split('/[@&]/', $predicate) ?: [] as $atom) {
                        if (preg_match('/^distance_type==([1-4])$/', trim($atom))) {
                            $exactCount++;
                        }
                    }
                }
            }

            if ($exactCount > 1) {
                DB::table('skills')
                    ->where('id', $skill->id)
                    ->update(['distance_type' => null]);
                $updated++;
            }
        }

        // $this->command?->info("Normalized {$updated} multi-token distance_type rows to NULL");
        Log::info("Normalized {$updated} multi-token distance_type rows to NULL");
    }

    public function down(): void
    {
        // The up() step is a data normalisation, not a schema change: it nulls `distance_type` on rows
        // whose `condition_groups` carry more than one exact `distance_type==N` token, because a single
        // integer column cannot represent a conjunction. The original tokens are still in
        // `condition_groups`, so re-running `uma:reparse skills` (or the import) recomputes the values
        // from the source export; there is nothing for this method to restore and nothing is lost.
        //
        // This must not log. It previously did — `Log::warn('Down not implemented…')` — and that call
        // both contradicted the "no stub bodies" rule and broke every `migrate:rollback` on this
        // application: Laravel 13 ships Monolog 3, whose logger exposes `warning()`, not `warn()`, so
        // the method threw `Call to undefined method Monolog\Logger::warn()`. A `down()` that cannot
        // run is the failure this application's own reversibility test exists to catch.
    }
};

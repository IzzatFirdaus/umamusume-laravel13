<?php

declare(strict_types=1);

use App\Models\Skill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
        // Down is not reversible without the original file state.
        // The import command is idempotent and will restore correct values from the export.
        Log::warn('Down not implemented; re-run import to restore distance_type from source.');
    }
};

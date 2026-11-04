<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix release_status for 7 skills where file=JapanOnly but table had GlobalReleased.
 *
 * Source export marks these skills with unreleased=['en'], so they should be JapanOnly.
 * The table incorrectly had GlobalReleased (likely from a prior import before the
 * unreleased logic was implemented correctly).
 *
 * Skills fixed (export_id -> name):
 * 100471: Raise My Soul's Blade!
 * 110161: Free from the Ashes
 * 110671: Rain Cloud Bolt
 * 110681: Bring on the Banquet!
 * 202491: Razor-Sharp Steps
 * 202492: Sharp Steps
 * 1000013: Carnival Bonus
 */
return new class extends Migration
{
    public function up(): void
    {
        $ids = [
            100471,
            110161,
            110671,
            110681,
            202491,
            202492,
            1000013,
        ];

        foreach ($ids as $exportId) {
            DB::table('skills')
                ->where('export_id', $exportId)
                ->where('release_status', 'GlobalReleased')
                ->update(['release_status' => 'JapanOnly']);
        }
    }

    public function down(): void
    {
        // Down restores the previous (incorrect) state for rollback testing
        $ids = [
            100471,
            110161,
            110671,
            110681,
            202491,
            202492,
            1000013,
        ];

        foreach ($ids as $exportId) {
            DB::table('skills')
                ->where('export_id', $exportId)
                ->where('release_status', 'JapanOnly')
                ->update(['release_status' => 'GlobalReleased']);
        }
    }
};
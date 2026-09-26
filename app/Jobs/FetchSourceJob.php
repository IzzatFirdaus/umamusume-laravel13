<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

/**
 * Queued wrapper around uma:fetch for one source (ARCHITECTURE §5). Unique per
 * source_key on the database queue, so a web-triggered refresh and a scheduled
 * run cannot double-burn the same rate-limited source.
 */
final class FetchSourceJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public string $uniqueId;

    public function __construct(public readonly string $sourceKey)
    {
        $this->uniqueId = $sourceKey;
    }

    public function handle(): void
    {
        Artisan::call('uma:fetch', ['source' => $this->sourceKey]);
    }
}

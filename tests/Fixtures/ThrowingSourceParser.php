<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use App\Services\DataPipeline\Contracts\SourceParser;

/**
 * A parser that always throws, for the per-source containment case.
 *
 * `UmaFetch` had no `catch` at all, so any parser exception ended the whole run and left every later
 * key unfetched. NFR-2 requires the opposite: a failed fetch degrades to a log entry plus retained
 * previous data. This fixture is the smallest thing that raises from inside a real parse.
 */
final class ThrowingSourceParser implements SourceParser
{
    /**
     * @return list<array{name: string}>
     */
    public function parse(string $body): array
    {
        throw new RuntimeException('This source is a deliberate failure fixture.');
    }
}

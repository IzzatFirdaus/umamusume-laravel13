<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use App\Services\DataPipeline\Contracts\SourceParser;

/**
 * Test-only parser: the fixture body is already JSON records, so parsing is a decode.
 * Real sources get one dedicated parser class each (AGENTS.md Data Engineer rules).
 */
final class TestSourceParser implements SourceParser
{
    /**
     * @return list<array{name: string, name_ja?: string|null, release_status?: string|null, jp_debut_date?: string|null, global_debut_date?: string|null, external_ref?: string|null}>
     */
    public function parse(string $body): array
    {
        /** @var list<array{name: string, name_ja?: string|null, release_status?: string|null, jp_debut_date?: string|null, global_debut_date?: string|null, external_ref?: string|null}> $records */
        $records = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        return $records;
    }
}

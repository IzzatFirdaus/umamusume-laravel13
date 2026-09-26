<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Contracts;

/**
 * One parser per declared source (config('uma.sources')[key]['parser']).
 * Parsers receive the raw snapshot body and return plain records;
 * they never write to the database and never issue network requests.
 */
interface SourceParser
{
    /**
     * @return list<array{name: string, name_ja?: string|null, release_status?: string|null, jp_debut_date?: string|null, global_debut_date?: string|null, external_ref?: string|null}>
     */
    public function parse(string $body): array;
}

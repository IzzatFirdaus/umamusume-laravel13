<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Contracts;

/**
 * The entry point for the four race-enrichment sources (`gametora-racetracks`,
 * `gametora-race-fans`, `gametora-races`, `gametora-race-rewards`).
 *
 * **Why this is separate from `SourceParser`.** That contract is the character-card shape: its
 * docblock names `name`, `release_status`, `jp_debut_date` and the other roster keys, because every
 * implementation behind it is stored as a character record. The enrichment sources answer different
 * questions — a track name, a fan payout, a course/direction/season triple, a reward list — and the
 * only consumer is `uma:enrich-races`, which joins them by id and never stores them as roster rows.
 * Declaring them as `SourceParser` made every implementation statically incompatible with the
 * contract it claimed to satisfy (four PHPStan errors, 2026-11-03); this contract states the one
 * thing they do share: a snapshot body in, a list of records out.
 *
 * The record shape is deliberately `array<string, mixed>`: each source's keys are its own, and the
 * command that reads them knows which source it is reading. A later source that needs a typed shape
 * should add its own contract, the way `RaceCatalogSourceParser` and `SkillSourceParser` already do.
 */
interface RaceEnrichmentSourceParser
{
    /**
     * @return list<array<string, mixed>>
     */
    public function parse(string $body): array;
}

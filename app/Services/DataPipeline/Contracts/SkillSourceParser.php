<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Contracts;

/**
 * Reads the skill catalogue into reference rows.
 *
 * Separate from `SourceParser` for the reason `RaceCatalogSourceParser` and
 * `ScenarioSourceParser` are separate: a skill's name is not a display name a
 * Trainer's own rows get cross-referenced against, so routing these records
 * through the match and review stages would file thousands of skills as pending
 * Umamusume candidates. `PipelineRunner::run()` branches on this contract and
 * writes straight through `App\Actions\StoreSkills`.
 *
 * Rows land in `skills`, whose shape is `PRD.md` FR-D-1 as amended 2026-09-29;
 * `docs/adr/0011-skills-reference-import.md` carries the measurements behind
 * every field below.
 */
interface SkillSourceParser
{
    /**
     * @return list<array{
     *     export_id: int,
     *     name: string,
     *     name_ja: string|null,
     *     name_is_client: bool,
     *     release_status: string,
     *     rarity: int|null,
     *     is_unique: bool,
     *     sp_cost: int|null,
     *     type: string|null
     * }>
     */
    public function parse(string $body): array;
}

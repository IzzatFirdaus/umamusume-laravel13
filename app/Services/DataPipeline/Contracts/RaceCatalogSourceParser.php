<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Contracts;

/**
 * Reads the career race catalogue into reference rows.
 *
 * Deliberately separate from SourceParser, for the same reason as
 * ScenarioSourceParser: a race carries no display name that a Trainer's own rows
 * would be cross-referenced against, so it must never enter the match and review
 * stages. Routing it through them would queue every race as an unmatched character.
 *
 * Rows land in `race_catalog_slots`, which ADR-0003 Amendment R3 assigns to the
 * fetch engine rather than to a seeder.
 */
interface RaceCatalogSourceParser
{
    /**
     * @return list<array{
     *     export_slot_id: string,
     *     scenario_key: string|null,
     *     year: int,
     *     month: int|null,
     *     half: string|null,
     *     turn: int|null,
     *     slot_label: string,
     *     title: string,
     *     tier: string|null,
     *     grade_code: int,
     *     distance: int|null,
     *     distance_band: string|null,
     *     surface: string|null,
     *     track_id: int|null,
     *     race_id: int|null,
     *     fans_needed: int|null,
     *     fans_gain_curve: int|null,
     *     is_mandatory: bool,
     *     is_maiden_gated: bool,
     *     is_special_race: bool,
     *     did_not_exist: string|null,
     *     external_ref: string|null,
     *     sort_order: int
     * }>
     */
    public function parse(string $body): array;
}

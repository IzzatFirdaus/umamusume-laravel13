<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Contracts;

/**
 * Reads a scenario dataset into reference rows. Deliberately separate from
 * SourceParser: scenarios carry no display name to cross-reference against a
 * Trainer's own rows, so they never enter the match and review stages (ADR-0004).
 */
interface ScenarioSourceParser
{
    /**
     * @return list<array{slug: string, name: string, name_ja?: string|null, order?: int|null,
     *                   jp_start_date?: string|null, global_start_date?: string|null,
     *                   cap_speed: int, cap_stamina: int, cap_power: int, cap_guts: int, cap_wit: int,
     *                   hard_cap?: int|null, caps_reworked_at?: string|null, external_ref?: string|null}>
     */
    public function parse(string $body): array;
}

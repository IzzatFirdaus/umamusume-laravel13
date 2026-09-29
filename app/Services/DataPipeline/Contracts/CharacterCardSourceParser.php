<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Contracts;

/**
 * A source whose rows are cards rather than characters (PRD FR-A-6, ADR-0008).
 *
 * Separate from SourceParser for the same reason ScenarioSourceParser is: a card
 * carries no name to cross-reference against a trainee. Its identity is the
 * source's own card id, so it belongs outside the match and review stage.
 */
interface CharacterCardSourceParser
{
    /**
     * @return list<array{card_id: int, char_external_ref: string, title: string,
     *                          rarity: int, global_release_date: string, is_debut_form: bool}>
     */
    public function parse(string $body): array;
}

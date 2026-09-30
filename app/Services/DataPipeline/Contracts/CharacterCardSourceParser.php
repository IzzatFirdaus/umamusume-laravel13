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
     * The two skill lists are `list<int>` of the source's own export ids, and a card the document
     * gives no lists for yields `[]` rather than null — KI-33. A source parser's contract is the
     * shape the writer may rely on, so it is stated here as well as in the implementation.
     *
     * @return list<array{card_id: int, char_external_ref: string, title: string,
     *                          rarity: int, global_release_date: string, is_debut_form: bool,
     *                          skills_innate: list<int>, skills_unique: list<int>}>
     */
    public function parse(string $body): array;
}

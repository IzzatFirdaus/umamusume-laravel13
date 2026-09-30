<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Contracts;

/**
 * Reads the GameTora support-card dataset into `support_cards` reference rows (ADR-0014).
 *
 * Separate from `SourceParser` for the reason `SkillSourceParser`,
 * `CharacterCardSourceParser` and `ProfileSourceParser` are separate: a support card's identity
 * is the source's own `support_id`, and the export publishes no single display name to
 * cross-reference against a Trainer's own rows (it publishes a character name and an epithet
 * separately, which `SupportCard::displayName()` composes at the view boundary). Routed through
 * the match stage, all 559 records would fail to name an Umamusume and land in `match_candidates`
 * as a review queue holding the entire support-card catalogue.
 *
 * `PipelineRunner::run()` branches on this contract and writes straight through
 * `App\Actions\StoreSupportCards`.
 */
interface SupportCardSourceParser
{
    /**
     * `effects` is the export's own anchor vector, unexpanded: one `list<int>` of exactly twelve,
     * the effect id followed by the eleven card-level anchors (levels 1, 5, 10 … 50), where `-1`
     * means the client holds no entry at that level and is not a zero
     * (UMAMUSUME_REFERENCE.md §1.4.7, ADR-0014 correction 4).
     *
     * `type` is one of the seven export keys held in `SupportCard::TYPES`, and `rarity` one of the
     * three `CardRarity` cases; a record outside either domain is not emitted at all, because the
     * column CHECK would refuse the write and the whole run would roll back.
     *
     * @return list<array{
     *     support_id: int,
     *     char_id: int,
     *     char_name: string|null,
     *     name_ja: string|null,
     *     title_en: string|null,
     *     title_ja: string|null,
     *     rarity: int,
     *     type: string,
     *     release_jp: string|null,
     *     release_global: string|null,
     *     effects: list<list<int>>
     * }>
     */
    public function parse(string $body): array;
}

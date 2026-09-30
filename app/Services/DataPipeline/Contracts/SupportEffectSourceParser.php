<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Contracts;

/**
 * Reads the GameTora support-effect dictionary into `support_effects` rows (ADR-0014).
 *
 * The dictionary names the ids that appear at position 0 of every anchor vector on a support card,
 * so a screen can say "Friendship Bonus" instead of hardcoding a label per id. It carries no display
 * name a Trainer's own rows are cross-referenced against and no character ref, which is why it is its
 * own contract and not a second shape of `SupportCardSourceParser`: one row per effect, keyed by the
 * source's own `id`.
 *
 * `PipelineRunner::run()` branches on this contract and writes straight through
 * `App\Actions\StoreSupportEffects`.
 */
interface SupportEffectSourceParser
{
    /**
     * `calc` is verbatim from the record or null, and the null is the fact: across all 35 records only
     * four declare it (`mult` on ids 1, 27, 28 and `add` on id 19), and UMAMUSUME_REFERENCE.md §1.4.8
     * finds that exactly the effects declaring `calc` combine multiplicatively. This repository's word
     * for the others is prose, not a fourth mode, so it is never written here.
     *
     * @return list<array{
     *     effect_id: int,
     *     name_en: string,
     *     name_ja: string|null,
     *     calc: string|null,
     *     symbol: string|null,
     *     description_en: string|null
     * }>
     */
    public function parse(string $body): array;
}

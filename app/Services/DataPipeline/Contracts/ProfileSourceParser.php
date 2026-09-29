<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Contracts;

/**
 * A source whose rows are trainee profiles rather than names or cards.
 *
 * Separate from `SourceParser` for the same reason `CharacterCardSourceParser` is: a profile row
 * carries no display name to cross-reference against a trainee. Its identity is the source's own
 * `char_id`, and the trainee it belongs to is named by a ref that resolves through
 * `umamusume.external_ref` — the same resolution `CharacterCardSourceParser` uses, one grain up.
 */
interface ProfileSourceParser
{
    /**
     * @return list<array{char_external_ref: string, name_ja: string|null, va_ja: string|null,
     *                    va_en: string|null, birth_year: int|null, birth_month: int|null,
     *                    birth_day: int|null, height: int|null, three_sizes_b: int|null,
     *                    three_sizes_h: int|null, three_sizes_w: int|null}>
     */
    public function parse(string $body): array;
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CardRarity;
use App\Models\SupportCard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The support-card catalog query (PRD FR-B; the same facet contract as `SkillSearchRequest`).
 *
 * Read-only, so there is nothing to authorize: this tool has no auth surface (`PRD.md` NFR-1) and the
 * screen shows reference data the fetch engine wrote.
 *
 * **An unknown facet value is refused rather than ignored**, for the reason `SkillSearchRequest`
 * records: dropping it would answer a question nobody asked with the whole catalog, and a Trainer reads
 * a full list as the answer to the facet they picked. `page` is the exception and is left to the
 * paginator, because a page past the end is an empty page and cannot mislead anyone about the data.
 *
 * Every accepted value is read off a constant the schema also reads (`SupportCard::TYPES`,
 * `SupportCard::AVAILABILITIES`, `CardRarity`) rather than restated here, so an option the form offers
 * without a column behind it fails this rule instead of failing silently.
 */
class SupportCardSearchRequest extends FormRequest
{
    /**
     * The two orderings besides the default. Tokens, not labels: the view names them for a Trainer.
     */
    public const SORTS = ['rarity', 'released'];

    /**
     * Where a refused facet lands. The default is `back()`, which with no referer resolves to `/` and
     * sends the Trainer to the run list from a filter they typed on the card catalog. Naming the route
     * keeps the failure on the screen that caused it, where the field error is rendered beside the
     * picker it belongs to.
     */
    protected $redirectRoute = 'support-cards.index';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rarity' => ['nullable', Rule::in(array_map(
                static fn (CardRarity $case): string => (string) $case->value,
                CardRarity::cases()
            ))],
            'type' => ['nullable', Rule::in(SupportCard::TYPES)],
            'status' => ['nullable', Rule::in(SupportCard::AVAILABILITIES)],
            'sort' => ['nullable', Rule::in(self::SORTS)],
        ];
    }

    protected function prepareForValidation(): void
    {
        // A GET form submits an unchosen select as `""`, and an empty facet means "no narrowing", not
        // "the row whose value is the empty string".
        $this->merge(array_combine(
            $keys = ['rarity', 'type', 'status', 'sort'],
            array_map(fn (string $key): mixed => $this->input($key) === '' ? null : $this->input($key), $keys)
        ));
    }
}

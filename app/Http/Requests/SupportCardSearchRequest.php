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
     * The two servers the wireframe's filter offers, in its order (`support-cards.html:76`).
     *
     * @var list<string>
     */
    public const SERVERS = ['Global', 'JP'];

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
            // The card-name search (`support-cards.html:73`): a Trainer reads the row's printed label,
            // so the controller matches it against the three stored name fields rather than a chip.
            'q' => ['nullable', 'string', 'max:120'],
            'rarity' => ['nullable', Rule::in(array_map(
                static fn (CardRarity $case): string => (string) $case->value,
                CardRarity::cases()
            ))],
            'type' => ['nullable', Rule::in(SupportCard::TYPES)],
            'status' => ['nullable', Rule::in(SupportCard::AVAILABILITIES)],
            // The wireframe's Server facet (`support-cards.html:76`): two servers, Global default. The
            // three-state Availability facet above stays accepted for the Database-area route, but the
            // library page scopes to one server rather than repeating availability on every row.
            'server' => ['nullable', Rule::in(self::SERVERS)],
            'sort' => ['nullable', Rule::in(self::SORTS)],
        ];
    }

    protected function prepareForValidation(): void
    {
        // A GET form submits an unchosen select as `""`, and an empty facet means "no narrowing", not
        // "the row whose value is the empty string". A search box submits spaces the same way: a
        // whitespace-only ask would match nothing while reading as "search for nothing".
        $this->merge(array_combine(
            $keys = ['q', 'rarity', 'type', 'status', 'server', 'sort'],
            array_map(function (string $key): mixed {
                $value = $this->input($key);

                if (is_string($value)) {
                    $value = trim($value);
                }

                return $value === '' ? null : $value;
            }, $keys)
        ));
    }
}

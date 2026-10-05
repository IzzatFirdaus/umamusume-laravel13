<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CardRarity;
use App\Models\SupportCard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The card picker on the deck builder (SCREEN-007).
 *
 * The facets here are the ones a column answers for: `type`, `rarity` and the generated
 * `release_status`, plus a free-text search over the two name columns the source states. The brief's
 * longer list asks for more, and each of the rest has no column behind it, so none of them is offered
 * here rather than answered with a list a Trainer cannot trust:
 *
 * - **level** and **limit break** are runtime values. A card's level is `30 + 5 x breaks` and the
 *   break count is a constant of four (`UMAMUSUME_REFERENCE.md` §1.4.2), neither of which this tool
 *   records per card, so a facet would filter a value the deck does not hold.
 * - **skill**, **training bonus** and **race bonus** live inside the `effects` and `hint_skills` JSON
 *   vectors. They are answerable with a `json_each` scan, and they are deliberately not scanned here,
 *   because a facet that reads a blob column across five hundred rows is the query this screen does not
 *   need to carry on every page load. `SCR-SUP-001` carries the same four facets over the same four
 *   columns, and the two pickers stay readable the same way.
 *
 * Every accepted value is read off the same constant the schema reads (`SupportCard::TYPES`,
 * `SupportCard::AVAILABILITIES`, `CardRarity`, `DeckSlot::POSITIONS`), so an option the form offers
 * without a column behind it fails this rule instead of failing silently.
 */
class DeckPickerSearchRequest extends FormRequest
{
    /**
     * Where a refused facet lands. The default is `back()`, which with no referer resolves to `/` and
     * sends the Trainer to the dashboard from a filter they typed on the deck builder. Naming the
     * route keeps the failure on the screen that caused it.
     */
    protected $redirectRoute = 'runs.deck';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * The named route plus the run, because `redirectRoute` on its own builds `/training-runs/{run}/deck`
     * with the placeholder unfilled and the redirect 404s into a 500 rather than back onto the screen the
     * Trainer filtered. The run is a bound route parameter, so it is read from the route rather than
     * carried on the request.
     */
    protected function getRedirectUrl(): string
    {
        $run = $this->route('run');

        return $run === null ? route($this->redirectRoute) : route($this->redirectRoute, $run);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['nullable', Rule::in(SupportCard::TYPES)],
            'rarity' => ['nullable', Rule::in(array_map(
                static fn (CardRarity $case): string => (string) $case->value,
                CardRarity::cases()
            ))],
            'status' => ['nullable', Rule::in(SupportCard::AVAILABILITIES)],
            'query' => ['nullable', 'string', 'max:120'],
            // Not narrowed to the six positions. A slot number usually arrives from a URL the Trainer
            // edited or a tab restored, and refusing it would bounce them off a screen they were
            // already reading to tell them a picker target is out of range. The controller clamps it
            // to the first position instead, which is the rule `PageSize` already states for a page
            // size and the same judgement.
            'slot' => ['nullable', 'integer'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // A GET form submits an unchosen select as `""`, and an empty facet means "no narrowing", not
        // "the row whose value is the empty string".
        $this->merge(array_combine(
            $keys = ['type', 'rarity', 'status', 'query'],
            array_map(fn (string $key): mixed => $this->input($key) === '' ? null : $this->input($key), $keys)
        ));
    }
}

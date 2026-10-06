<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * The Veteran library's filter set (`GET /veterans`, PRD FR-G-2, `SCREEN-021`).
 *
 * It is `LegacySearchRequest` plus one thing. The three facets are exactly what `ListVeterans` answers,
 * the same three the Legacy Lab browse list offers, and the empty-means-no-filter handling, the unknown
 * scenario refusal and the `filters()` shape are already owned there. Restating them would put a second
 * copy of FR-G-2's vocabulary in the tree, and the two lists would drift.
 *
 * Only `$redirectRoute` changes: a refused filter on the library returns to the library, not to the
 * Legacy Lab. `order` is the library's own addition, because a list you choose a parent from is read top
 * to bottom and the browse surface's fixed order is not a choice the Trainer made.
 *
 * `order` sorts by the row's own id, which is the order the library recorded it in. It is deliberately not
 * a date sort: no column holds the date a career finished, so "oldest first" is a statement about this
 * list and the page says so. The sorts `SCREEN-021` names (Spark quality, aptitude, skill coverage, race
 * history, overall usefulness) are held computations and appear nowhere here.
 */
class VeteranSearchRequest extends LegacySearchRequest
{
    /**
     * @var string
     */
    protected $redirectRoute = 'veterans.index';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return parent::rules() + [
            'order' => ['nullable', 'string', Rule::in(['newest', 'oldest'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return parent::messages() + [
            'order.in' => 'The library is ordered newest first or oldest first, and nothing else.',
        ];
    }

    /**
     * A cleared order control arrives as the empty string, which `in` would refuse. The parent does the
     * same for the three facets; this extends that rule to the one key the parent does not know.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $order = $this->input('order');

        if (is_string($order) && trim($order) === '') {
            $this->merge(['order' => null]);
        }
    }

    /**
     * @return 'newest'|'oldest'
     */
    public function order(): string
    {
        /** @var array{order?: string|null} $validated */
        $validated = $this->validated();

        // `?? null` and not a bare read: `validated()` carries only the keys that arrived, so a request
        // with no order at all (every plain visit to the library) would raise an undefined-key error.
        return ($validated['order'] ?? null) === 'oldest' ? 'oldest' : 'newest';
    }
}

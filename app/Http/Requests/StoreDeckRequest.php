<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\DeckSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates the six-slot support deck a Trainer equips for a run (ADR-0014).
 *
 * The slot number is carried as the array key, which is what makes this request different from
 * `StoreRunSkillRequest` beside it. That one re-indexes its rows with `array_values` because a skill row
 * stands for itself; a deck row stands for a position, so re-indexing after dropping the blanks would
 * quietly move every card one slot down the moment a Trainer left slot two empty.
 */
class StoreDeckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // local-only tool; no auth surface (ARCHITECTURE §8)
    }

    /**
     * Drop the slots left on "Not equipped" before rules run, keeping the surviving keys. A blank slot
     * is a deliberate clear, not a missing field, so it must not reach `required` rules.
     */
    protected function prepareForValidation(): void
    {
        $deck = $this->input('deck');

        if (! is_array($deck)) {
            return;
        }

        $kept = array_filter(
            $deck,
            static fn (mixed $row): bool => is_array($row) && (string) ($row['support_card_id'] ?? '') !== ''
        );

        $this->merge(['deck' => $kept]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Key-restricted so a hand-made POST cannot address slot 9 and have it stored.
            'deck' => ['present', 'array:'.implode(',', DeckSlot::POSITIONS)],
            'deck.*.support_card_id' => ['required', 'integer', 'exists:support_cards,id'],
            // The owned-or-rented flag, bounded by `DeckSlot::OWNERSHIP` rather than by a copy of the two
            // words. Both writers record it now (`ADR-0023`, D3): the run-scoped write puts it on the
            // slot's `deck_slots.ownership` row, and the setup wizard's step 5 keeps it in the session
            // draft until Preflight creates that row (`StoreDraftDeckRequest::payload()`).
            // `nullable` because a slot the Trainer cleared carries no flag at all.
            'deck.*.ownership' => ['nullable', 'string', Rule::in(DeckSlot::OWNERSHIP)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'deck.present' => 'Send the six slots, including the ones left empty.',
            'deck.array' => 'A deck has slots one to six and nothing else.',
            'deck.*.support_card_id.required' => 'Pick a card for this slot, or set it to "Not equipped".',
            'deck.*.support_card_id.exists' => 'That card is not in the catalogue. Import the support cards first.',
            'deck.*.ownership.in' => 'A slot holds a card you own or one you rented, and nothing else.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        // Two copies of the same card cannot sit in one deck (UMAMUSUME_REFERENCE.md §1.4.5), which is
        // why duplicates are spent on breaking rather than equipped. Reported once against the whole
        // deck rather than per slot, because the fault is the pair and not either row.
        $validator->after(function (Validator $validator): void {
            $deck = (array) $this->input('deck');
            $ids = array_filter(array_map(static fn (mixed $row): mixed => is_array($row) ? ($row['support_card_id'] ?? null) : null, $deck));

            if ($ids !== array_unique($ids)) {
                $validator->errors()->add('deck', 'The same card cannot be equipped twice. Break the duplicate instead.');
            }
        });
    }

    /**
     * The deck as the controller wants it: position mapped to card id, positions with no card removed.
     *
     * @return array<int, int>
     */
    public function slotsByCardId(): array
    {
        $deck = (array) ($this->validated()['deck'] ?? []);

        return array_map(
            static fn (array $row): int => (int) $row['support_card_id'],
            $deck
        );
    }

    /**
     * The owned-or-rented flag per position, for the slots that carried one.
     *
     * A posted row carrying no flag sends null, and the write keeps whatever the slot already recorded
     * rather than erasing it (`ADR-0023`). A slot the
     * Trainer cleared carries no entry here at all, because the blank row is dropped before the rules
     * run and there is no card for the flag to describe.
     *
     * @return array<int, string|null>
     */
    public function ownershipByPosition(): array
    {
        $deck = (array) ($this->validated()['deck'] ?? []);
        $ownership = [];

        foreach ($deck as $position => $row) {
            $ownership[(int) $position] = isset($row['ownership']) ? (string) $row['ownership'] : null;
        }

        return $ownership;
    }
}

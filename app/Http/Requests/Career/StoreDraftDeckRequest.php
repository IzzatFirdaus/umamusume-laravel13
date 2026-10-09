<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use App\Http\Requests\StoreDeckRequest;
use App\Models\DeckSlot;
use Illuminate\Validation\Rule;

/**
 * The deck of the setup wizard's step 5 (`SCR-CAR-009`, PRD FR-A-4, `ADR-0020` §1, under `ADR-0014`).
 *
 * A subclass, not a second rule set. `rules()`, `messages()`, `prepareForValidation()` and the duplicate
 * refusal are `StoreDeckRequest`'s own, so the wizard and the run-scoped write (`runs.deck.sync`) accept
 * and reject exactly the same six positions and exactly the same catalogue ids, and `DeckSlot::POSITIONS`
 * stays the one owner of what a deck is.
 *
 * **What the draft holds that the run cannot yet hold.** The flag has a column now (`ADR-0023`, D3) and
 * Preflight writes it there, but a career does not exist until step 6, so this step's own record is the
 * session: `payload()` returns six rows in position order, each with the card it holds or `null` where
 * the Trainer left the slot on "Not equipped", and the ownership the Trainer set for a card that is
 * there. A cleared slot records no flag, because "owned or rented" describes nothing when no card sits
 * in the slot.
 *
 * Preflight (D7) writes the equipped rows and their flags through the same table
 * (`PreflightController::store()`), which `CareerPreflightTest` pins.
 */
class StoreDraftDeckRequest extends StoreDeckRequest
{
    /**
     * @var string
     */
    protected $redirectRoute = 'career.deck';

    /**
     * The wizard's one demand over the run-scoped write: a slot that carries a card has to say how it
     * is held.
     *
     * `StoreDeckRequest` lets the flag be absent, because a slot that keeps its card can keep the flag
     * the run already recorded. The draft is the opposite case: the flag is the only reason the key
     * exists, so a row reaching Preflight without one would print `N/A` for something the Trainer did
     * choose. An absent flag is therefore refused here rather than defaulted, because "owned" would be
     * an invented answer and `ADR-0020` §2 does not allow one.
     *
     * Blank slots never meet this rule: the parent's `prepareForValidation()` drops them before the
     * rules run, so every row that is evaluated here carries a card. The vocabulary stays
     * `DeckSlot::OWNERSHIP`'s, not a second copy of the two words.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'deck.*.ownership' => ['required', 'string', Rule::in(DeckSlot::OWNERSHIP)],
        ];
    }

    /**
     * The deck as the draft stores it: all six positions, in order, blanks included.
     *
     * The parent drops a blank slot before its rules run, because a deliberate clear is not a missing
     * field; the draft needs the gap, so the six rows are rebuilt from `DeckSlot::POSITIONS` rather than
     * from whatever the form happened to post.
     *
     * @return list<array{position: int, support_card_id: int|null, ownership: string|null}>
     */
    public function payload(): array
    {
        /** @var array<int, array<string, mixed>> $deck */
        $deck = (array) ($this->validated()['deck'] ?? []);

        $slots = [];

        foreach (DeckSlot::POSITIONS as $position) {
            $row = $deck[$position] ?? null;

            $slots[] = [
                'position' => $position,
                'support_card_id' => $row === null ? null : (int) $row['support_card_id'],
                'ownership' => $row === null || ! isset($row['ownership'])
                    ? null
                    : (string) $row['ownership'],
            ];
        }

        return $slots;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use App\Http\Requests\StoreDeckRequest;
use App\Models\DeckSlot;

/**
 * The deck of the setup wizard's step 5 (`SCR-CAR-009`, PRD FR-A-4, `ADR-0020` §1, under `ADR-0014`).
 *
 * A subclass, not a second rule set. `rules()`, `messages()`, `prepareForValidation()` and the duplicate
 * refusal are `StoreDeckRequest`'s own, so the wizard and the run-scoped write (`runs.deck.sync`) accept
 * and reject exactly the same six positions and exactly the same catalogue ids, and `DeckSlot::POSITIONS`
 * stays the one owner of what a deck is.
 *
 * **What the draft carries that the table cannot.** `deck_slots` has no ownership column (`ADR-0014`), and
 * inventing one is the owner's call, which is why the run-scoped builder prints the one value it can read
 * and says so in words. A session key needs no migration, so the wizard does carry the flag: `payload()`
 * returns six rows in position order, each with the card it holds or `null` where the Trainer left the slot
 * on "Not equipped", and the ownership the Trainer set for a card that is there. A cleared slot records no
 * flag, because "owned or rented" describes nothing when no card sits in the slot.
 *
 * Preflight (D7) writes the equipped rows through the same table; the flag stays in the draft until a
 * column exists for it, which `SCREEN_SPEC.md` records as this step's gap.
 */
class StoreDraftDeckRequest extends StoreDeckRequest
{
    /**
     * @var string
     */
    protected $redirectRoute = 'career.deck';

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

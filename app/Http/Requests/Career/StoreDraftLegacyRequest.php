<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use App\Http\Requests\StoreLegacySelectionRequest;

/**
 * The ancestry of the setup wizard's step 4 (`SCR-CAR-008`, PRD FR-G-1, `ADR-0020` §1 and §3).
 *
 * A subclass, not a second rule set. `rules()`, `messages()`, `withValidator()`, `payload()` and the two
 * cross-field refusals (the same library row twice, a parent who is the trainee herself) are
 * `StoreLegacySelectionRequest`'s own, so the wizard and the run-scoped write (`legacy.update`) can never
 * drift apart, and `LegacySelectionPayload` stays the one owner of the accepted vocabularies. The three
 * things overridden here are destinations and lifecycle, not rules:
 *
 * - `authorize()` is the parent's run-lifecycle guard, which belongs to a route that carries a run. This
 *   route carries none, and the draft has no status to be `Completed` or `Retired` about: the run does not
 *   exist yet, which is the whole reason `SetupDraft` exists.
 * - `getRedirectUrl()` sends a refusal back to the step that asked for it. The parent names a run because
 *   its route is parameterised by one; here the reason is the same and the target is different.
 * - `parentIds()` is the draft's half of what the run's two `inheritance_parent_*` columns hold for a
 *   live career (`ADR-0010` Decision: the payload carries the read-back, the columns carry the identity).
 *   A career with no row has nowhere to put an identity, so the draft keeps it and Preflight writes the
 *   pair with the json, exactly as `LegacyController::update()` does.
 *
 * The values are written under the draft's `legacy_selection` and `legacy_parents` keys by
 * `LegacySelectController::store()`, never to a run row.
 */
class StoreDraftLegacyRequest extends StoreLegacySelectionRequest
{
    public function authorize(): bool
    {
        return true; // local-only tool, and no run exists at this step yet (ARCHITECTURE §8)
    }

    public function getRedirectUrl(): string
    {
        return route('career.legacy');
    }

    /**
     * The two library rows the Trainer picked, in slot order, null for a slot left unchosen.
     *
     * These are `veterans` ids, the entered fact, and not the `umamusume` ids the run's two foreign keys
     * hold: the pick control selects a library row, so the draft can echo what was chosen back into it.
     * Preflight resolves each to its trainee the way `LegacyController::parentUmamusumeIds()` does for a
     * live write, because a Veteran is a run whose trainee is the Umamusume the client shows in the slot.
     *
     * @return array{0: int|null, 1: int|null}
     */
    public function parentIds(): array
    {
        /** @var list<array<string, mixed>> $legacies */
        $legacies = (array) $this->validated('legacies', []);

        $ids = array_map(
            static fn (array $legacy): ?int => isset($legacy['legacy_id']) ? (int) $legacy['legacy_id'] : null,
            $legacies,
        );

        return [$ids[0] ?? null, $ids[1] ?? null];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Http\Requests\Career\StoreRosterMemberRequest;
use App\Models\RosterMember;
use App\Models\TrainingRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The Unity Cup team roster's write doors (D01).
 *
 * §1.8 of `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` records the client's own panel: `Members:
 * 20 / 20`, laid out as five distance columns with an ACE plus two more in each. This controller is
 * the backend behind that panel — create, edit, remove — scoped to one run and never deriving a
 * member from the six-card deck.
 *
 * **Run isolation is the nested binding.** Every route nests `{member}` under `{run}`, so a member
 * posted to another run's roster is a 404 before any write. The three methods all assert the binding
 * agrees, because a route model binding on both sides of a nested route is the shape where a
 * mismatched pair would otherwise slip through.
 *
 * **Team Rank is never touched here.** ADR-0034's stored `team_rank` column is the run's manually
 * recorded league standing, and `TeamEditTest` covers its write path. No method in this controller
 * reads or writes it, and D02's regression test pins that a full roster edit leaves it alone.
 *
 * **The 20-member ceiling** is enforced in `RosterMember::booted()`'s `saving` guard rather than
 * here, so a writer that is not this controller cannot pass it. A `RuntimeException` from that guard
 * is translated here into a validation error the form prints, because the alternative is a 500 on a
 * limit the Trainer can see and understand.
 */
class RosterMemberController extends Controller
{
    /**
     * Add one member to the roster.
     */
    public function store(StoreRosterMemberRequest $request, TrainingRun $run): RedirectResponse
    {
        $this->assertRosterApplies($run);

        $payload = $request->payload();

        // `position` appends rather than being posted: the roster reads in this order and the
        // Trainer adding a member is adding her at the end.
        $payload['position'] = ($run->rosterMembers()->max('position') ?? 0) + 1;

        try {
            DB::transaction(fn () => $run->rosterMembers()->create($payload));
        } catch (\RuntimeException $e) {
            // The model guard raises one exception type for both the roster-full and the
            // ACE-clash cases; the form prints each beside the field it came from, so the message
            // chooses the key.
            throw ValidationException::withMessages([
                $this->guardErrorKey($e->getMessage()) => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('runs.team', $run)
            ->with('status', 'Team member added.');
    }

    /**
     * Edit one member's recorded attributes.
     */
    public function update(StoreRosterMemberRequest $request, TrainingRun $run, RosterMember $member): RedirectResponse
    {
        $this->assertRosterApplies($run);
        $this->assertMemberBelongsToRun($run, $member);

        try {
            DB::transaction(fn () => $member->update($request->payload()));
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages([
                'distance_assignment' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('runs.team', $run)
            ->with('status', 'Team member updated.');
    }

    /**
     * Remove one member from the roster.
     */
    public function destroy(TrainingRun $run, RosterMember $member): RedirectResponse
    {
        $this->assertRosterApplies($run);
        $this->assertMemberBelongsToRun($run, $member);

        DB::transaction(fn () => $member->delete());

        return redirect()
            ->route('runs.team', $run)
            ->with('status', 'Team member removed.');
    }

    /**
     * Which field a roster-guard error belongs beside in the form.
     *
     * The model's `saving` guard raises one `RuntimeException` for both the roster-full and the
     * ACE-clash cases, so the controller cannot branch on the exception type. The two messages name
     * different fields: the ceiling is about the roster as a whole, the ACE is about a column.
     */
    private function guardErrorKey(string $message): string
    {
        return str_contains($message, 'ACE') ? 'distance_assignment' : 'name';
    }

    /**
     * The roster is the Unity Cup's own, so a run whose scenario records no team has no roster
     * to write. `teamEdit()` gates on the same `team_rank_ladder` capability, so the editor and
     * the write routes agree about which runs may carry a roster.
     */
    private function assertRosterApplies(TrainingRun $run): void
    {
        abort_unless(
            $run->composesPanel('team_rank_ladder'),
            422,
            'This scenario records no team roster.'
        );
    }

    /**
     * A member bound to a run that is not the route's run is a 404, not a silent cross-write.
     */
    private function assertMemberBelongsToRun(TrainingRun $run, RosterMember $member): void
    {
        abort_unless($member->training_run_id === $run->id, 404);
    }
}

<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

/**
 * TD-08 (AUDIT:D-2): the five historical conflict-involvement states.
 *
 * The open facet (`in_open_conflict`, `open_conflict_count`) answers "is this source in a LIVE
 * dispute". It cannot answer what a source's history is, because a resolved row leaves the open
 * payload — so a source whose only conflict was settled would read as clear. These cases pin the
 * separate `history_involvement` state across all five outcomes, and the one rule that matters most:
 * missing evidence is `unknown`, never `none`.
 */
it('reports open-only involvement for a source named by an open conflict', function (): void {
    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('fetched', fn ($rows): bool => collect($rows)
                ->firstWhere('name', 'gametora-race-catalog')['history_involvement'] === 'open'));
});

it('reports resolved-only involvement, and does not call the source clear', function (): void {
    // The 1400 m conflict settled: it leaves `conflicts` (open-only, unchanged) but the source it
    // named must not read as `none` — it has history.
    config()->set('reference.conflicts.rows.0.state', 'resolved');

    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $props = $page->toArray()['props'];
            $row = collect($props['fetched'])->firstWhere('name', 'gametora-race-catalog');

            expect($row['history_involvement'])->toBe('resolved')
                ->and($row['in_open_conflict'])->toBeFalse()
                ->and($row['open_conflict_count'])->toBe(0)
                ->and($row['resolved_conflicts'])->toContain('conflict-distance-band-1400m')
                ->and($props['conflicts'])->not->toContain('conflict-distance-band-1400m')
                ->and(collect($props['conflict_history'])->pluck('id')->all())
                ->toContain('conflict-distance-band-1400m');
        });
});

it('reports mixed involvement when a source is named by both an open and a resolved conflict', function (): void {
    config()->set('reference.conflicts.rows.0.state', 'resolved');
    config()->set('reference.conflicts.rows.1.affected_sources', ['gametora-race-catalog']);

    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('fetched', fn ($rows): bool => collect($rows)
                ->firstWhere('name', 'gametora-race-catalog')['history_involvement'] === 'mixed'));
});

it('reports no recorded involvement only when the records do establish their sources', function (): void {
    // Every conflict names its sources, so an unnamed row is genuinely clear rather than unproven.
    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $props = $page->toArray()['props'];
            $row = collect($props['fetched'])->firstWhere('name', 'gametora-characters');

            // Two conflict rows carry `evidence_available: false`, so an unnamed source is
            // `unknown` (missing evidence), never `none` (a clean bill of health).
            expect($row['history_involvement'])->toBe('unknown')
                ->and($row['history_involvement_note'])->toContain('missing evidence')
                ->and($props['conflict_evidence_unavailable'])->toBeTrue();
        });
});

it('reports unavailable historical evidence rather than no involvement', function (): void {
    // A conflict that could not establish its sources makes every unnamed source unproven, not clear.
    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $props = $page->toArray()['props'];

            expect($props['conflict_evidence_unavailable'])->toBeTrue();

            foreach (collect($props['fetched']) as $row) {
                expect($row['history_involvement'])->not->toBe('none');
            }
        });
});

it('keeps the open facet separate from the historical state', function (): void {
    // Resolving every row empties the open facet while history stays reported.
    foreach (range(0, 3) as $i) {
        config()->set("reference.conflicts.rows.$i.state", 'resolved');
    }

    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $props = $page->toArray()['props'];

            expect($props['conflicts'])->toBe([])
                ->and(collect($props['fetched'])->pluck('in_open_conflict')->unique()->all())->toBe([false])
                ->and(collect($props['fetched'])->pluck('history_involvement')->all())
                ->toContain('resolved');
        });
});

it('discloses evidence gaps independently when known involvement also exists', function (): void {
    // The distance-band source is named by an open conflict (known involvement), yet two other
    // conflict rows carry `evidence_available: false`. The known state must be stated as fact while
    // the gap is disclosed separately — never silently treated as exhaustive.
    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $props = $page->toArray()['props'];
            $row = collect($props['fetched'])->firstWhere('name', 'gametora-race-catalog');

            expect($row['history_involvement'])->toBe('open')
                ->and($row['history_involvement_note'])->toContain('Named by an open conflict')
                ->and($row['history_involvement_note'])->not->toContain('No conflict record')
                ->and($props['conflict_evidence_unavailable'])->toBeTrue();
        });
});

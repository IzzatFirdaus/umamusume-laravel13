<?php

declare(strict_types=1);

use App\Models\DataSource;
use App\Models\RaceCatalogSlot;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * N2.11: the Data sources screen's fetch date is a union of two recorded arms.
 *
 * `data_sources` is the ledger of requests this tool has made, but most reference tables also carry
 * their own per-row stamp (`source_url` + `fetched_at`, ADR-0003 Amendment R3). The screen used to read
 * only the ledger, so a dataset whose ledger key had no row said "no fetch recorded" while its rows
 * carried a real date. These tests pin the union, the channel label, and the one thing it must never do:
 * borrow a date for a source that neither arm has stamped.
 */
it('dates a dataset from its own inline row stamp when the ledger has no key for it', function (): void {
    $url = (string) config('uma.sources')['gametora-support-effects']['url'];

    DB::table('support_effects')->insert([
        'effect_id' => 'test-friendship',
        'name_en' => 'Friendship Bonus',
        'source_url' => $url,
        'fetched_at' => '2026-11-05 15:06:27',
    ]);

    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Database/Sources')
            ->where('fetched', function (Collection $rows): bool {
                $effect = $rows->firstWhere('name', 'gametora-support-effects');
                $artwork = $rows->firstWhere('name', 'gametora-artwork');

                return $effect !== null
                    && $effect['fetched_at'] === '2026-11-05 15:06:27'
                    && $effect['channel'] === 'inline'
                    // The asset host has no document fetch to record; absence stays absence.
                    && $artwork !== null
                    && $artwork['fetched_at'] === null
                    && $artwork['channel'] === null;
            }));
});

it('labels the ledger as the channel when its stamp is newer than the inline one', function (): void {
    $url = (string) config('uma.sources')['gametora-support-effects']['url'];

    DB::table('support_effects')->insert([
        'effect_id' => 'test-friendship',
        'name_en' => 'Friendship Bonus',
        'source_url' => $url,
        'fetched_at' => '2026-11-01 10:00:00',
    ]);

    DataSource::factory()->create([
        'source_key' => 'gametora-support-effects',
        'url' => $url,
        'fetched_at' => '2026-11-09 10:00:00',
    ]);

    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Database/Sources')
            ->where('fetched', fn (Collection $rows): bool => $rows->firstWhere('name', 'gametora-support-effects')['fetched_at'] === '2026-11-09 10:00:00'
                && $rows->firstWhere('name', 'gametora-support-effects')['channel'] === 'data_sources'));
});

it('labels the inline arm as the channel when its stamp is newer than the ledger', function (): void {
    $url = (string) config('uma.sources')['gametora-support-effects']['url'];

    DB::table('support_effects')->insert([
        'effect_id' => 'test-friendship',
        'name_en' => 'Friendship Bonus',
        'source_url' => $url,
        'fetched_at' => '2026-11-20 10:00:00',
    ]);

    DataSource::factory()->create([
        'source_key' => 'gametora-support-effects',
        'url' => $url,
        'fetched_at' => '2026-11-09 10:00:00',
    ]);

    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Database/Sources')
            ->where('fetched', fn (Collection $rows): bool => $rows->firstWhere('name', 'gametora-support-effects')['fetched_at'] === '2026-11-20 10:00:00'
                && $rows->firstWhere('name', 'gametora-support-effects')['channel'] === 'inline'));
});

it('splits a comma-separated provenance list into one stamp per source URL', function (): void {
    RaceCatalogSlot::factory()->create([
        'source_url' => implode(', ', [
            (string) config('uma.sources')['gametora-racetracks']['url'],
            (string) config('uma.sources')['gametora-races']['url'],
        ]),
        'fetched_at' => '2026-11-19 17:59:33',
    ]);

    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Database/Sources')
            ->where('fetched', function (Collection $rows): bool {
                $racetracks = $rows->firstWhere('name', 'gametora-racetracks');
                $races = $rows->firstWhere('name', 'gametora-races');

                // One enriched race row names several documents; each named URL gets the row's stamp,
                // rather than the whole list being kept as one unusable key.
                return $racetracks !== null
                    && $racetracks['fetched_at'] === '2026-11-19 17:59:33'
                    && $racetracks['channel'] === 'inline'
                    && $races !== null
                    && $races['fetched_at'] === '2026-11-19 17:59:33'
                    && $races['channel'] === 'inline';
            }));
});

/**
 * N6.5 (D-2): the conflict-involvement facet.
 *
 * The plan's Sources filters call for an `in-open-conflict` narrowing, and the 2026-11-21 audit recorded
 * that no source↔conflict association existed to filter on: a conflict carried `affected_routes` (screens)
 * but named no dataset. These cases pin the association the config now authors and the two directions the
 * controller derives from it — each conflict's `affected_sources`, and each row's `conflicts` — including
 * the three states a Trainer has to be able to tell apart: a row in dispute, a row nobody disputes, and a
 * conflict that reaches no row at all.
 */
it('names the ledger rows each conflict is about, keyed by the name the row prints', function (): void {
    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $props = $page->toArray()['props'];
            $byId = collect($props['conflicts'])->keyBy('id');

            // §7 row 7's Source A is the data export, so the dispute reaches the rows that export feeds.
            expect($byId['conflict-distance-band-1400m']['affected_sources'])
                ->toContain('gametora-race-catalog')
                ->toContain('gametora-racetracks')
                ->toContain('gametora-skills');

            // And every name a conflict carries is a row the ledger actually prints. A name no row holds
            // would render as a filter option that matches nothing.
            $names = collect($props['fetched'])->pluck('name')
                ->merge(collect($props['transcribed'])->pluck('name'))
                ->all();

            foreach ($props['conflicts'] as $conflict) {
                foreach ((array) $conflict['affected_sources'] as $name) {
                    expect($names)->toContain($name);
                }
            }
        });
});

it('stamps each row with the open conflicts that name it, and an empty list when none do', function (): void {
    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $props = $page->toArray()['props'];
            $fetched = collect($props['fetched'])->keyBy('name');
            $transcribed = collect($props['transcribed'])->keyBy('name');

            // Affected: the disputed band is stored on the race rows.
            expect($fetched['gametora-race-catalog']['conflicts'])->toContain('conflict-distance-band-1400m')
                ->and($fetched['gametora-racetracks']['conflicts'])->toContain('conflict-distance-band-1400m');

            // Unaffected, and not null: "no recorded disagreement" is its own answer, and the screen's
            // empty state has to be able to say so rather than reading an absence as unread.
            expect($fetched['gametora-characters']['conflicts'])->toBe([])
                ->and($transcribed['Recurring event types']['conflicts'])->toBe([])
                ->and($fetched['gametora-artwork']['conflicts'])->toBe([]);

            // Both directions agree: no conflict names a row that does not carry it back.
            foreach ($props['conflicts'] as $conflict) {
                foreach ((array) $conflict['affected_sources'] as $name) {
                    $row = $fetched->get($name) ?? $transcribed->get($name);
                    expect($row['conflicts'])->toContain($conflict['id']);
                }
            }
        });
});

it('leaves a resolved conflict out of every row and out of its own affected list', function (): void {
    // A conflict the owner has ruled resolved leaves the open list rather than being carried as history:
    // the page is headed "Open conflicts" and a settled row must not sit under it. The rows it named stop
    // naming it with no second edit, because only open conflicts are indexed.
    config()->set('reference.conflicts.rows.0.state', 'resolved');

    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $props = $page->toArray()['props'];
            $ids = collect($props['conflicts'])->pluck('id')->all();

            expect($ids)->not->toContain('conflict-distance-band-1400m')
                ->and($ids)->toHaveCount(3);

            $stamped = collect($props['fetched'])->pluck('conflicts')->flatten()->all();

            expect($stamped)->not->toContain('conflict-distance-band-1400m');
        });
});

it('never lets a conflict name a source the ledger does not hold', function (): void {
    // The association is authored as config keys and resolved through the rows the arms built. A key with
    // no row is dropped, not printed: a filter option that matches nothing is worse than no option.
    config()->set('reference.conflicts.rows.0.affected_sources', ['gametora-races', 'no-such-source']);

    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $conflict = collect($page->toArray()['props']['conflicts'])
                ->firstWhere('id', 'conflict-distance-band-1400m');

            expect($conflict['affected_sources'])->toBe(['gametora-races']);
        });
});

/*
 * TD-08: the historical involvement facet, in the five states the record can support.
 *
 * The distinction these cases exist for is the one a two-state "in a conflict / not in a conflict"
 * boolean cannot make: a source named only by settled rows is NOT in a live dispute but is also not
 * a source nobody has ever doubted, and a source the records could not place is not a clean one.
 * The fixtures are `config()` overrides on a scratch request, so no production conflict is invented
 * and no stored row is written.
 */
it('reports open, resolved, mixed, none and unknown involvement as five distinct states', function (): void {
    config()->set('reference.conflicts.rows', [
        [
            'id' => 'conflict-fixture-open',
            'question' => 'A fixture question that is still open',
            'claims' => ['fixture side one', 'fixture side two'],
            'state' => 'open',
            'affected_routes' => [],
            'affected_sources' => ['gametora-races', 'gametora-racetracks'],
        ],
        [
            'id' => 'conflict-fixture-resolved',
            'question' => 'A fixture question the owner settled',
            'claims' => ['fixture side one', 'fixture side two'],
            'state' => 'resolved',
            'affected_routes' => [],
            'affected_sources' => ['gametora-racetracks', 'gametora-skills'],
        ],
        // No `evidence_available: false` row here on purpose: this case is about the four states a
        // placeable record can produce, and an unplaced record in the same set would make every
        // unnamed source `unknown` for the right reason. That case is the next test.
    ]);

    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $props = $page->toArray()['props'];
            $fetched = collect($props['fetched'])->keyBy('name');

            expect($props['conflict_evidence_unavailable'])->toBeFalse();

            // Named by an open record and by no settled one.
            expect($fetched['gametora-races']['history_involvement'])->toBe('open')
                ->and($fetched['gametora-races']['in_open_conflict'])->toBeTrue()
                ->and($fetched['gametora-races']['resolved_conflicts'])->toBe([]);

            // Named by BOTH: the live dispute and the settled history are reported separately, and the
            // combined state is the third word rather than either of the two.
            expect($fetched['gametora-racetracks']['history_involvement'])->toBe('mixed')
                ->and($fetched['gametora-racetracks']['conflicts'])->toBe(['conflict-fixture-open'])
                ->and($fetched['gametora-racetracks']['resolved_conflicts'])->toBe(['conflict-fixture-resolved'])
                ->and($fetched['gametora-racetracks']['open_conflict_count'])->toBe(1);

            // Named only by a settled record: not in a live dispute, but not untouched either.
            expect($fetched['gametora-skills']['history_involvement'])->toBe('resolved')
                ->and($fetched['gametora-skills']['in_open_conflict'])->toBeFalse()
                ->and($fetched['gametora-skills']['conflicts'])->toBe([])
                ->and($fetched['gametora-skills']['resolved_conflicts'])->toBe(['conflict-fixture-resolved']);

            // Named by nothing, and every record DID establish its sources: a real clean answer.
            expect($fetched['gametora-characters']['history_involvement'])->toBe('none')
                ->and($fetched['gametora-characters']['in_open_conflict'])->toBeFalse();

            // The per-source note travels with the state, so the screen can explain it in place.
            expect($fetched['gametora-racetracks']['history_involvement_note'])
                ->toContain('open conflict')
                ->toContain('resolved');
        });
});

it('never reports an unplaced source as clear when a record could not establish its sources', function (): void {
    config()->set('reference.conflicts.rows', [
        [
            'id' => 'conflict-fixture-unplaced',
            'question' => 'A fixture question whose claims named no dataset',
            'claims' => ['fixture side one', 'fixture side two'],
            'state' => 'open',
            'affected_routes' => [],
            'affected_sources' => [],
            'evidence_available' => false,
        ],
    ]);

    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $props = $page->toArray()['props'];

            expect($props['conflict_evidence_unavailable'])->toBeTrue();

            // Every row the record does not name is `unknown`, never `none`: an unestablished
            // association is a gap, and `none` would read as a finding.
            foreach ($props['fetched'] as $row) {
                expect($row['history_involvement'])->toBe('unknown')
                    ->and($row['history_involvement_note'])->toContain('missing evidence');
            }

            foreach ($props['transcribed'] as $row) {
                expect($row['history_involvement'])->toBe('unknown');
            }
        });
});

it('keeps resolved rows out of the open panel and available as their own history', function (): void {
    config()->set('reference.conflicts.rows', [
        [
            'id' => 'conflict-fixture-open',
            'question' => 'A fixture question that is still open',
            'claims' => ['fixture side one', 'fixture side two'],
            'state' => 'open',
            'affected_routes' => [],
            'affected_sources' => [],
        ],
        [
            'id' => 'conflict-fixture-resolved',
            'question' => 'A fixture question the owner settled',
            'claims' => ['settled side one', 'settled side two'],
            'state' => 'resolved',
            'affected_routes' => [],
            'affected_sources' => [],
        ],
    ]);

    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $props = $page->toArray()['props'];

            // The open panel's semantics are unchanged: it carries the open record only.
            expect(collect($props['conflicts'])->pluck('id')->all())->toBe(['conflict-fixture-open']);

            // And the settled one is still shipped, so the screen can name it as history rather
            // than leaving "we settled this" indistinguishable from "we never looked".
            expect(collect($props['conflict_history'])->pluck('id')->all())->toBe(['conflict-fixture-resolved']);
        });
});

it('carries the parser-specific Supplies phrase, and an absence rather than an invented one', function (): void {
    $this->get('/database/sources')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $props = $page->toArray()['props'];
            $fetched = collect($props['fetched'])->keyBy('name');

            // N6.2: the Supplies column names what the parser ingests, per source, rather than one
            // generic pipeline sentence repeated on every row.
            expect($fetched['gametora-race-catalog']['note'])->toContain('race catalogue');
            expect($fetched['gametora-support-effects']['note'])->toContain('Support-effect');
            expect($fetched['gametora-artwork']['note'])->toContain('artwork');

            // A source whose parser phrase is not recorded falls back to the generic sentence
            // rather than claiming a capability this build cannot cite.
            expect($fetched['gametora-characters']['note'])->toContain('Character catalogue');

            // Two rows do not share one sentence, which is what the per-parser metadata is for.
            expect($fetched['gametora-races']['note'])
                ->not->toBe($fetched['gametora-support-cards']['note']);
        });
});

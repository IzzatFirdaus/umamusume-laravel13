<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

/*
 * UMAMUSUME_REFERENCE.md §1.2.6: "The maiden lock is a run-level state, not a per-race property."
 *
 * The export states the rule once, on the maiden row, as a sentence about *every* standard race —
 * "You can't participate in any races listed here until you win either Debut or any of the Maiden
 * Races" — and never as a flag per race. A schema that models it as a per-row `is_maiden_gated`
 * column has invented a field the source does not carry per row.
 *
 * The three columns this pins as absent were all nullified by their own writers (the catalogue
 * parser and the scenario seeder both seeded `false`), so the lock never fired from them; the live
 * read path is `TrainingRun::maidenGates()` over `hasClearedMaidenLock()` (won Debut, grade 900, or
 * Maiden, grade 800, at placement 1). `2026_10_27_120000_drop_per_race_maiden_gate_columns` drops
 * them, and this file is the durable evidence independent of whether that migration has been run
 * against any particular database.
 *
 * Cited requirement: US-10 (race calendar and fan gating).
 */

it('carries no per-race maiden gate on the race catalogue', function (): void {
    expect(Schema::getColumnListing('race_catalog_slots'))->not->toContain('is_maiden_gated');
});

it('carries no per-race maiden gate on the scenario slots', function (): void {
    expect(Schema::getColumnListing('scenario_slots'))->not->toContain('is_maiden_gated');
});

it('carries no per-race maiden gate on the legacy scenario races', function (): void {
    expect(Schema::getColumnListing('scenario_races'))->not->toContain('maiden_gated');
});

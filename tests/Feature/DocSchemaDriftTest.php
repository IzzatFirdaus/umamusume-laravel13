<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * ADR-0008 / the AGENTS.md Architect rule "schema changes require a migration
 * plus updated ESSENTIALS digest in the same change": a governance doc that goes
 * on describing an applied migration as un-landed is a defect, not a wording nit.
 * The next implementer reads ADR-0008 as the truth of the schema and codes against
 * a column the tree already has or one it has already dropped.
 *
 * Two review rounds have corrected this class on this branch (`c02600e`,
 * `cd26d8d`) and the second named why the drift survived the first:
 * `tests/Feature/CharacterCardSchemaTest.php:56-81` pins the shipped
 * `character_cards` column list, so nothing read the `training_runs` side, and no
 * test anywhere read these docs against the applied-migration set. This file
 * covers both halves: the columns, and the wording.
 *
 * Deliberately narrow, because a guard that fires on honest forward-looking prose
 * gets deleted within a task:
 *
 * - Four named docs, not the repo. `PLAN.md`, `docs/scenarios/**`,
 *   `docs/UMAMUSUME_REFERENCE.md`, `KNOWN-ISSUES.md` and
 *   `docs/design-research/verification/**` plan later work or record history, so
 *   "this has not landed yet" is correct prose there and asserting against them
 *   would be retro-asserting the past.
 * - The three un-landed wordings this branch has actually shipped, taken from the
 *   `cd26d8d` diff. A fresh paraphrase slips past the pin; that is the price of a
 *   guard a human can read, and the vocabulary is one line to widen here when a
 *   paraphrase bites.
 * - The dated-erratum blocks this repo's own correction convention keeps. ADR-0008
 *   leaves each superseded paragraph standing as written and records the landing
 *   beside it, so the run column's placeholder wording at `:21` is governed by
 *   that convention rather than by this pin.
 */

/**
 * The governance docs whose schema prose this guard reads.
 *
 * @return list<string>
 */
function governanceDocPaths(): array
{
    return [
        'docs/adr/0008-character-card-catalog-layer.md',
        'ARCHITECTURE.md',
        'ARCHITECTURE-ESSENTIALS.md',
        'docs/design-research/CONSTRAINTS.md',
    ];
}

/**
 * The migration file names the test store actually carries, read from the
 * `migrations` table rather than from `database/migrations`, so the guard is never
 * more current than the schema RefreshDatabase migrated and holds on any driver.
 *
 * @return list<string>
 */
function appliedMigrationFileNames(): array
{
    /** @var list<string> $applied */
    $applied = DB::table('migrations')->pluck('migration')->all();

    return $applied;
}

/**
 * Every shipped table with the columns it really has, the second way a doc can
 * name the object it is describing.
 *
 * @return array<string, list<string>>
 */
function landedColumnsByTable(): array
{
    $landed = [];

    foreach (Schema::getTables() as $table) {
        $name = (string) ($table['name'] ?? '');

        if ($name === '') {
            continue;
        }

        $landed[$name] = Schema::getColumnListing($name);
    }

    return $landed;
}

/**
 * Doc lines that call a landed schema object un-landed.
 *
 * @param  list<string>  $applied  migration file names from the `migrations` table
 * @param  array<string, list<string>>  $landed  table name => its real columns
 * @return list<string> one entry per offending line: `path:line` then the line itself
 */
function unlandedDocClaims(array $applied, array $landed): array
{
    // `authorized, NOT migrated yet` (the ARCHITECTURE-ESSENTIALS wording),
    // `the next migration in the slice` and `lands as 2026_…` (both
    // ARCHITECTURE.md §3's) - the three phrasings `cd26d8d` had to correct.
    $unlandedClaim = '/\bnot (?:migrated yet|yet migrated)\b'
        .'|\bnext migration in the slice\b'
        .'|\blands? (?:as|in)\s+[`\']?\d{4}_\d{2}_\d{2}_\d{6}/i';

    $offenders = [];

    foreach (governanceDocPaths() as $doc) {
        if (! is_file(base_path($doc))) {
            $offenders[] = $doc.': the drift guard reads this doc and it is not there';

            continue;
        }

        $lines = preg_split('/\R/', (string) file_get_contents(base_path($doc))) ?: [];

        foreach ($lines as $index => $line) {
            if (preg_match($unlandedClaim, $line) !== 1) {
                continue;
            }

            if (! namesLandedObject($line, $applied, $landed)) {
                continue;
            }

            $offenders[] = $doc.':'.($index + 1).'  '.trim(substr($line, 0, 140));
        }
    }

    return $offenders;
}

/**
 * Whether a line names something the schema already ships: an applied migration,
 * by its file name or the timestamp prefix the docs quote, or a real table named
 * beside one of that table's real columns. `_` is a word character, so the `\b`
 * tests cannot match a short column inside a longer identifier such as
 * `support_card_id`, which is what keeps prose about a genuinely un-landed column
 * out of the offender list.
 *
 * @param  list<string>  $applied
 * @param  array<string, list<string>>  $landed
 */
function namesLandedObject(string $line, array $applied, array $landed): bool
{
    foreach ($applied as $migration) {
        if (str_contains($line, $migration)) {
            return true;
        }

        if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})/', $migration, $timestamp) === 1
            && str_contains($line, $timestamp[1])) {
            return true;
        }
    }

    foreach ($landed as $table => $columns) {
        if (preg_match('/\b'.preg_quote($table, '/').'\b/', $line) !== 1) {
            continue;
        }

        foreach ($columns as $column) {
            if (preg_match('/\b'.preg_quote($column, '/').'\b/', $line) === 1) {
                return true;
            }
        }
    }

    return false;
}

it('ships the two columns the roster docs describe as applied', function (): void {
    // ADR-0008's Decision table, ARCHITECTURE.md §3, the ESSENTIALS digest lines
    // and D-30 all name `training_runs.character_card_id` and
    // `umamusume.external_ref`. The column-list pin in `CharacterCardSchemaTest`
    // holds `character_cards` only, so these two are also the entries the doc
    // guard's table-to-column inventory depends on: drop either key column and its
    // subject branch silently stops matching anything.
    expect(Schema::getColumnListing('training_runs'))
        ->toContain('character_card_id')
        ->and(Schema::getColumnListing('umamusume'))
        ->toContain('external_ref');
});

it('keeps no governance doc describing an applied migration as un-landed', function (): void {
    $applied = appliedMigrationFileNames();
    $landed = landedColumnsByTable();
    $trainingRunColumns = $landed['training_runs'] ?? [];

    // A guard whose inputs came back empty passes for the wrong reason, so both
    // inputs are floored before the offender list is asserted.
    expect($applied)
        ->toContain('2026_09_29_120200_add_character_card_id_to_training_runs_table')
        ->and($trainingRunColumns)->toContain('character_card_id');

    // Each entry is `path:line` plus the line's own text, so a breakage names the
    // sentence to correct rather than just failing a count.
    expect(unlandedDocClaims($applied, $landed))->toBe([]);
});

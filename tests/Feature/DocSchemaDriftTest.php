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
 *
 * There is NO dated-erratum exemption, and the guard does not have one. ADR-0008's
 * superseded paragraphs (`:10-13`, `:21`) are preserved as written by this repo's
 * correction convention, and they escape this pin only because their wording happens
 * to sit outside the three-phrase vocabulary: `authorized, not built` and
 * `the slice's next migration` match nothing here. That is coverage by accident, not
 * a designed pass, and it is fragile in both directions. Forward: a future correction
 * that quotes a guarded phrase about a landed object — quoting `NOT migrated yet` is
 * exactly what a dated erratum exists to preserve — fails CI on prose the convention
 * requires to stand, and the wrong fix would be to widen the vocabulary or delete the
 * guard; the right fix is to restate the quotation so it names the state at the time
 * (`was authorized while the schema did not yet have it`) and leave the pin alone.
 * Backward: an exemption keyed on the marker cannot work, because the marker sits on
 * the correction block while the text the convention protects is the earlier
 * paragraph, which the correction identifies only in prose and with a varying count
 * ("the sentence above", "the paragraph above", "the two paragraphs above"); keying on
 * "a dated correction paragraph" instead would blind this guard to the three
 * dated-correction paragraphs already standing in `docs/design-research/CONSTRAINTS.md`
 * alone, none of which has anything to do with the schema. The test below pins the
 * no-exemption behaviour so the claim and the code cannot drift apart again.
 *
 * Both helpers are line-scoped, and a wrapped phrase escapes them: the export reads
 * each doc as separate lines, so a guarded wording that straddles a soft wrap
 * (`not migrated` at the end of one line, `yet` at the start of the next) matches
 * nothing. All four governance docs hard-wrap prose, each at its own width, so this is a real
 * blind spot, disclosed rather than fixed — fixing it means joining paragraphs before
 * matching, which would widen the guard's reach across every other line of them.
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
 * The framework's own tables are left out, by {@see frameworkTableNames()}, because
 * their columns are ordinary English words rather than schema vocabulary.
 *
 * @return array<string, list<string>>
 */
function landedColumnsByTable(): array
{
    $landed = [];

    foreach (Schema::getTables() as $table) {
        $name = (string) ($table['name'] ?? '');

        if ($name === '' || in_array($name, frameworkTableNames(), true)) {
            continue;
        }

        $landed[$name] = Schema::getColumnListing($name);
    }

    return $landed;
}

/**
 * The tables the `0001_01_01_*` skeleton migrations create, plus `migrations` itself,
 * which the migrator writes.
 *
 * They are excluded because a column name is only evidence of a schema object when the
 * word means itself: `migrations.id` and `jobs.queue` are the words `id` and `queue`,
 * so a governance doc describing this guard's own inputs ("the applied migration set
 * from the `migrations` table") would name a landed object on nothing but the word
 * `id`, and a dated correction about the guard could trip the pin it is correcting. No
 * governance doc here claims a framework table is un-landed, so the exclusion costs
 * this pin nothing it can currently see.
 *
 * @return list<string>
 */
function frameworkTableNames(): array
{
    return [
        'cache',
        'cache_locks',
        'failed_jobs',
        'job_batches',
        'jobs',
        'migrations',
        'password_reset_tokens',
        'sessions',
        'users',
    ];
}

/**
 * The three un-landed wordings, in one pattern so widening the vocabulary stays the
 * one-line edit the docblock above says it is.
 */
function unlandedClaimPattern(): string
{
    // `authorized, NOT migrated yet` (the ARCHITECTURE-ESSENTIALS wording),
    // `the next migration in the slice` and `lands as 2026_…` (both
    // ARCHITECTURE.md §3's) - the three phrasings `cd26d8d` had to correct.
    return '/\bnot (?:migrated yet|yet migrated)\b'
        .'|\bnext migration in the slice\b'
        .'|\blands? (?:as|in)\s+[`\']?\d{4}_\d{2}_\d{2}_\d{6}/i';
}

/**
 * Doc lines that call a landed schema object un-landed, across the four governance docs.
 *
 * @param  list<string>  $applied  migration file names from the `migrations` table
 * @param  array<string, list<string>>  $landed  table name => its real columns
 * @return list<string> one entry per offending line: `path:line` then the phrase that matched
 */
function unlandedDocClaims(array $applied, array $landed): array
{
    $offenders = [];

    foreach (governanceDocPaths() as $doc) {
        if (! is_file(base_path($doc))) {
            $offenders[] = $doc.': the drift guard reads this doc and it is not there';

            continue;
        }

        $offenders = [
            ...$offenders,
            ...unlandedClaimsInDoc($doc, (string) file_get_contents(base_path($doc)), $applied, $landed),
        ];
    }

    return $offenders;
}

/**
 * The same pin over one doc body, so a planted claim can be tested without editing a
 * tracked governance doc: the offender set is a property of the wording plus the
 * schema, and both are inputs here.
 *
 * @param  list<string>  $applied
 * @param  array<string, list<string>>  $landed
 * @return list<string> one entry per offending line: `path:line` then the phrase that matched
 */
function unlandedClaimsInDoc(string $doc, string $body, array $applied, array $landed): array
{
    $offenders = [];

    foreach (preg_split('/\R/', $body) ?: [] as $index => $line) {
        if (preg_match(unlandedClaimPattern(), $line, $matched) !== 1) {
            continue;
        }

        if (! namesLandedObject($line, $applied, $landed)) {
            continue;
        }

        // The matched phrase and its line number, never a prefix of the line. The
        // D-30 rule is one 1,794-byte line whose offending clause sits around byte
        // 1,500, so `substr($line, 0, 140)` printed the rule's heading instead of the
        // wording that broke the pin — and byte slicing can cut a multibyte character
        // in half. The match is a whole token run, so it cannot.
        $offenders[] = $doc.':'.($index + 1).'  matched “'.trim($matched[0]).'”';
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
 * Residual, and disclosed rather than patched: the co-occurrence test is still a
 * heuristic over app tables whose column names are ordinary English (`title`,
 * `status`, `notes`), so a line that names such a table anywhere in prose counts as
 * naming a landed object. Widening the table name requirement to backtick-quoted
 * identifiers would blind the real drift this pin was written for, which arrives as
 * plain prose in the ESSENTIALS digest, so the loose form stands.
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

    // Each entry is `path:line` plus the phrase that matched, so a breakage names the
    // wording to correct rather than just failing a count.
    expect(unlandedDocClaims($applied, $landed))->toBe([]);
});

it('flags a planted un-landed claim and stays green on a pending one', function (): void {
    // The four lines `cd26d8d` and `7539750` had to correct, planted here rather than
    // back into the tracked docs: this is the proof the pin still catches real drift,
    // and it does not need the tree to be wrong to demonstrate it. The expected set
    // also shows the printing rule — the leftmost guarded phrase, not the line's head,
    // which is what a 1,794-byte rule line could offer instead.
    $applied = appliedMigrationFileNames();
    $landed = landedColumnsByTable();
    $body = <<<'MD'
    # Digest

    - training_runs: character_card_id? authorized, **NOT migrated yet**
    - `training_runs.character_card_id` is the next migration in the slice
    - stays the required owner. Not migrated yet — lands as 2026_09_29_120200_add_card…
    - `character_card_id` FK->character_cards nullable, lands as 2026_09_29_120200_add…
    - the card store action lands as 2099_01_01_000000_add_card_store_columns
    MD;

    expect(unlandedClaimsInDoc('digest.md', $body, $applied, $landed))->toBe([
        'digest.md:3  matched “NOT migrated yet”',
        'digest.md:4  matched “next migration in the slice”',
        'digest.md:5  matched “Not migrated yet”',
        'digest.md:6  matched “lands as 2026_09_29_120200”',
    ])
        // The last line is honest forward-looking prose: the same wording, for a
        // migration the applied set does not carry. A guard that fired on it would be
        // deleted within a task, which is why it is asserted green here rather than
        // left to inference.
        ->and(unlandedClaimsInDoc('digest.md', '- the card store action lands as 2099_01_01_000000_add_card_store_columns', $applied, $landed))->toBe([]);
});

it('exempts no dated correction paragraph, because the guard has no such path', function (): void {
    // The docblock above claims there is no erratum exemption, and ADR-0008 :50-52
    // used to claim the opposite. This test is what keeps the two honest: quote a
    // guarded phrase about a landed object inside a dated correction, in the exact form
    // this repo's convention writes one, and the pin still fires. If a vague
    // "skip dated corrections" hole is ever added here, this test is what fails.
    $applied = appliedMigrationFileNames();
    $landed = landedColumnsByTable();
    $body = <<<'MD'
    **Second correction, dated 2026-09-29 — the earlier paragraph stands as written.**
    It said `training_runs.character_card_id` was authorized and **not migrated yet**,
    and named the slice's next migration for it. Both readings are false today.
    MD;

    expect(unlandedClaimsInDoc('0008-character-card-catalog-layer.md', $body, $applied, $landed))->toBe([
        '0008-character-card-catalog-layer.md:2  matched “not migrated yet”',
    ]);
});

it('does not read a framework table word as a landed schema object', function (): void {
    // `migrations` is a real table and `id` is one of its columns, so before
    // `frameworkTableNames()` excluded the skeleton tables, a correction describing
    // this guard's own inputs matched on the ordinary words `migrations` and `id` and
    // the pin tripped on itself.
    $applied = appliedMigrationFileNames();
    $landed = landedColumnsByTable();
    $selfReference = '- the drift guard reads the applied set from the `migrations` table by `id`, which was not migrated yet as an app table';

    expect(array_intersect(['migrations', 'users', 'jobs'], array_keys($landed)))->toBe([])
        ->and(namesLandedObject($selfReference, $applied, $landed))->toBeFalse()
        ->and(unlandedClaimsInDoc('drift.md', $selfReference, $applied, $landed))->toBe([]);

    // The exclusion is narrow: drop the framework words and name an app table beside
    // one of its real columns, and the same sentence fires again.
    expect(unlandedClaimsInDoc('drift.md', '- `training_runs` holds `character_card_id`, which was not migrated yet', $applied, $landed))->toBe([
        'drift.md:1  matched “not migrated yet”',
    ]);
});

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

/**
 * KI-50: a column-level `->check(...)` in a migration is a silent no-op on SQLite. `ColumnDefinition`
 * stores any unknown method name as a Fluent attribute and the SQLite grammar has no `modifyCheck`, so the
 * DDL that comes out carries no CHECK token while the source reads as a guarantee the database does not
 * enforce. `2026_09_30_142618` wrote four of them; the hand-written raw SQL in `2026_09_30_151945` put the
 * constraints in for real, and `enum()` is the one Laravel construct that renders a CHECK at all.
 *
 * Nothing in the suite read the emitted DDL before this, so a migration that dropped one of these
 * constraints would have been invisible. The pairs below are the constrained domains this schema declares,
 * read out of `sqlite_master` on the migrated test database rather than out of the migration source.
 */
function tableDdl(string $table): string
{
    $row = DB::selectOne('SELECT sql FROM sqlite_master WHERE type = ? AND name = ?', ['table', $table]);

    return strtolower((string) $row->sql);
}

it('enforces every declared domain with a real CHECK clause', function (string $table, string $column): void {
    expect(tableDdl($table))->toContain('check ("'.$column.'"');
})->with([
    ['deck_slots', 'slot_position'],
    ['support_cards', 'rarity'],
    ['support_cards', 'type'],
    ['support_effects', 'calc'],
    ['scenario_slots', 'half'],
    ['race_catalog_slots', 'half'],
]);

it('constrains those columns to the values the domain actually names', function (): void {
    expect(tableDdl('support_effects'))->toContain("in ('mult', 'add')")
        ->and(tableDdl('scenario_slots'))->toContain("in ('early', 'late')")
        ->and(tableDdl('deck_slots'))->toContain('between 1 and 6');
});

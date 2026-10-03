<?php

declare(strict_types=1);

use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Support\Facades\DB;

/**
 * KI-41: the name-ordered reads ended with `orderBy('name')` and nothing after it, so the order was
 * total only because the data happened to have no two trainees whose names compare equal. What a Trainer
 * reads is "the same page of names every time", and a paginated list whose tie is unresolved can move a
 * row across a page boundary between two loads of the same data.
 *
 * The tiebreaker is invisible while the names are unique, and two same-name rows still come back in id
 * order on SQLite today, so an assertion on a rendered order would be a check that cannot fail. This
 * asserts the ORDER BY clause the app actually runs.
 */
function nameOrderClause(string $url, string $table = 'umamusume'): string
{
    DB::enableQueryLog();

    test()->get($url)->assertOk();

    $clause = '';

    foreach (DB::getQueryLog() as $entry) {
        if (str_contains($entry['query'], 'from "'.$table.'"')
            && preg_match('/order by [^"]*"name"[^()]*/i', $entry['query'], $m) === 1) {
            $clause = $m[0];
            break;
        }
    }

    DB::disableQueryLog();

    return $clause;
}

it('breaks the run form roster tie on the primary key', function (): void {
    Umamusume::factory()->create(['name' => 'Tied Name']);

    expect(nameOrderClause(route('runs.create')))->toMatch('/"name" asc, "id" asc/');
});

it('breaks the catalog page tie on the primary key, which is what fixes the page boundary', function (): void {
    Umamusume::factory()->create(['name' => 'Tied Name']);

    expect(nameOrderClause(route('catalog.index')))->toMatch('/"name" asc, "id" asc/');
});

it('breaks the trainee json list tie on the primary key', function (): void {
    Umamusume::factory()->create(['name' => 'Tied Name']);

    expect(nameOrderClause(route('api.v1.umamusume.index')))->toMatch('/"name" asc, "id" asc/');
});

it('breaks the run screen skill select tie on the primary key', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    Skill::factory()->create(['name' => 'Tied Skill']);

    expect(nameOrderClause(route('runs.show', $run), 'skills'))->toMatch('/"name" asc, "id" asc/');
});

it('breaks the import screen trainee list tie on the primary key', function (): void {
    Umamusume::factory()->create(['name' => 'Tied Name']);

    expect(nameOrderClause(route('runs.import')))->toMatch('/"name" asc, "id" asc/');
});

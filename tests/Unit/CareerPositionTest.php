<?php

declare(strict_types=1);

use App\Domain\Career\CareerPosition;
use App\Enums\CareerPhase;
use App\Enums\CareerYear;

it('refuses a month outside a calendar year', function (int $month): void {
    new CareerPosition(CareerYear::Junior, $month, CareerPhase::Early, 1);
})->throws(InvalidArgumentException::class, 'A career month is 1 to 12;')
    ->with([[0], [13]]);

it('refuses a turn index outside a standard Global career', function (int $turn): void {
    new CareerPosition(CareerYear::Junior, 1, CareerPhase::Early, $turn);
})->throws(InvalidArgumentException::class, 'A career turn index is 1 to 72;')
    ->with([[0], [73]]);

it('refuses a negative scenario countdown', function (): void {
    new CareerPosition(CareerYear::Senior, 10, CareerPhase::Early, 67, -1);
})->throws(InvalidArgumentException::class, 'A scenario countdown counts turns and cannot be negative; [-1] is not.');

it('round-trips through the stored shape', function (): void {
    $position = new CareerPosition(CareerYear::Senior, 10, CareerPhase::Early, 67, 5);

    expect(CareerPosition::fromArray($position->toArray())->toArray())->toBe($position->toArray());
});

it('refuses a stored year the calendar does not name', function (): void {
    CareerPosition::fromArray(['year' => 4, 'month' => 1, 'phase' => 'Early', 'turn_index' => 73]);
})->throws(InvalidArgumentException::class, 'A career year is one of Junior, Classic, Senior; [4] is not.');

it('refuses a stored phase the calendar does not name', function (): void {
    CareerPosition::fromArray(['year' => 1, 'month' => 1, 'phase' => 'Mid', 'turn_index' => 1]);
})->throws(InvalidArgumentException::class, 'A career phase is one of Early, Late; [\'Mid\'] is not.');

it('refuses a stored position whose turn index disagrees with its own calendar', function (): void {
    // Senior Year Early October is turn 67; the payload claims 12, which is Junior.
    CareerPosition::fromArray(['year' => 3, 'month' => 10, 'phase' => 'Early', 'turn_index' => 12]);
})->throws(InvalidArgumentException::class, 'turn index disagrees with its calendar');

it('requires the fields a position cannot be derived without', function (string $key): void {
    $payload = ['year' => 3, 'month' => 10, 'phase' => 'Early', 'turn_index' => 67];
    unset($payload[$key]);

    CareerPosition::fromArray($payload);
})->throws(InvalidArgumentException::class)
    ->with([['year'], ['month'], ['phase'], ['turn_index']]);

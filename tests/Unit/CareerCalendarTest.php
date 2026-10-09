<?php

declare(strict_types=1);

use App\Domain\Career\CareerPosition;
use App\Enums\CareerPhase;
use App\Enums\CareerYear;
use App\Services\CareerCalendar;

it('opens a standard Global career at Junior Year Early January and closes it at Senior Year Late December', function (): void {
    $first = CareerCalendar::turnIndexToPosition(1);
    $last = CareerCalendar::turnIndexToPosition(CareerCalendar::TURNS_PER_CAREER);

    expect($first->year)->toBe(CareerYear::Junior)
        ->and($first->month)->toBe(1)
        ->and($first->phase)->toBe(CareerPhase::Early)
        ->and($last->year)->toBe(CareerYear::Senior)
        ->and($last->month)->toBe(12)
        ->and($last->phase)->toBe(CareerPhase::Late);
});

it('round-trips every turn of a 72-turn career through both directions', function (): void {
    for ($turn = 1; $turn <= CareerCalendar::TURNS_PER_CAREER; $turn++) {
        $position = CareerCalendar::turnIndexToPosition($turn);

        expect(CareerCalendar::positionToTurnIndex($position))->toBe($turn)
            ->and($position->turnIndex)->toBe($turn)
            ->and(CareerCalendar::positionToTurnIndex(CareerPosition::fromArray($position->toArray())))->toBe($turn);
    }
});

it('reads the year boundary the way the catalogue does: turn 24 is Junior Late December and 25 is Classic Early January', function (): void {
    $juniorEnd = CareerCalendar::turnIndexToPosition(24);
    $classicStart = CareerCalendar::turnIndexToPosition(25);

    expect($juniorEnd->year)->toBe(CareerYear::Junior)
        ->and($juniorEnd->month)->toBe(12)
        ->and($juniorEnd->phase)->toBe(CareerPhase::Late)
        ->and($classicStart->year)->toBe(CareerYear::Classic)
        ->and($classicStart->month)->toBe(1)
        ->and($classicStart->phase)->toBe(CareerPhase::Early);
});

it('gives an odd turn the Early half of its month and an even turn the Late half', function (): void {
    $early = CareerCalendar::turnIndexToPosition(19);
    $late = CareerCalendar::turnIndexToPosition(20);

    expect($early->month)->toBe(10)->and($early->phase)->toBe(CareerPhase::Early)
        ->and($late->month)->toBe(10)->and($late->phase)->toBe(CareerPhase::Late);
});

it('refuses a turn outside a standard Global career', function (): void {
    CareerCalendar::turnIndexToPosition(0);
})->throws(InvalidArgumentException::class, 'A career turn index is 1 to 72; [0] is not.');

it('refuses a turn beyond Senior Year Late December rather than clamping it into a false position', function (): void {
    CareerCalendar::turnIndexToPosition(73);
})->throws(InvalidArgumentException::class, 'A career turn index is 1 to 72; [73] is not.');

it('carries a scenario countdown through the position without letting an absent one become a zero', function (): void {
    $withCountdown = CareerCalendar::turnIndexToPosition(67, 5);

    expect($withCountdown->scenarioCountdown)->toBe(5)
        ->and(CareerCalendar::turnIndexToPosition(67)->scenarioCountdown)->toBeNull();
});

it('names the twelve months and refuses anything else', function (): void {
    expect(CareerCalendar::monthLabel(1))->toBe('January')
        ->and(CareerCalendar::monthLabel(10))->toBe('October')
        ->and(CareerCalendar::monthLabel(12))->toBe('December');

    CareerCalendar::monthLabel(13);
})->throws(InvalidArgumentException::class, 'A career month is 1 to 12; [13] is not.');

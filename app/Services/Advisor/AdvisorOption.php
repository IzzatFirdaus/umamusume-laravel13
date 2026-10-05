<?php

declare(strict_types=1);

namespace App\Services\Advisor;

/**
 * One ranked option the advisor offers for the next turn (FR-F-3, `ADR-0020` §2).
 *
 * `energyAfter` is a range rather than a number for every training action, because the cost it is
 * derived from is a range; `Rest` and `Wit` resolve to a single figure and carry it as a range whose
 * ends are equal, so a surface never has to branch on which kind of option it is holding.
 *
 * `band` is null when the run has recorded no Energy, which is not the same as either band and not
 * the same as `RiskNotMeasured`: null means the question could not be asked, while
 * `RiskNotMeasured` is the answer for `Wit` specifically (`ADR-0001` §3 correction).
 */
final readonly class AdvisorOption
{
    /**
     * @param  array{min: int, max: int}|null  $energyAfter  null when no Energy is recorded
     */
    public function __construct(
        public string $action,
        public int $deficitClosed,
        public ?array $energyAfter,
        public ?string $band,
        public string $reason,
    ) {}
}

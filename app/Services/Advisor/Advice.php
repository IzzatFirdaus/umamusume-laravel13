<?php

declare(strict_types=1);

namespace App\Services\Advisor;

/**
 * What the advisor has to say about one turn (FR-F-2/3, `ADR-0020` §2).
 *
 * The shape carries no score and no numeric confidence, and that absence is the design: both are
 * unsourced (`ADR-0001` §3, `ADR-0020` §2), so the contract has no field for them to arrive in.
 *
 * `absence` is a sentence rather than a boolean, because the reasons the advisor declines to rank
 * are different from one another and a Trainer acting on the answer needs to know which one they
 * hit: no Energy recorded, no target set, or every stat already at its target.
 */
final readonly class Advice
{
    /**
     * @param  list<AdvisorOption>  $options  every option that could be derived, ranked or not
     */
    public function __construct(
        public ?string $band,
        public ?AdvisorOption $recommendation,
        public ?AdvisorOption $alternative,
        public array $options,
        public ?string $absence,
        /**
         * Where this run stands against its scenario's finale, or null when the scenario carries no
         * finale row or the finale is not close enough to be worth a line. Shape:
         * `array{label: string, state: string, turns_away: int|null}`.
         *
         * It is context, not a recommendation: the engine ranks the turn and never advises on the
         * concert, because no rule here covers concert preparation and the gauge that would decide it
         * is unbuilt (`ADR-0020` §3, `SCREEN_SPEC.md` §7-22).
         */
        public ?array $finale_context = null,
    ) {}
}

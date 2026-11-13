<?php

declare(strict_types=1);

namespace Tests\Feature\Career;

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\Career\TeamPanelBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Spirit Burst payout forecast, and the two readings of the burst tally it reports.
 *
 * Two facts about this payout are not settled by the evidence. **Whether Extreme Spirit Bursts count**
 * toward the band: GameTora counts both, other descriptions state a threshold without saying. **The turn
 * it lands on**: no published source places it and this application maps no turn to a calendar month, so
 * the forecast states the timing as calendar vocabulary and prints no countdown arithmetic.
 *
 * These cases pin both answers, including the boundary pairs where the two readings disagree, so a later
 * change cannot quietly pick one reading and call it the rule.
 */
class SpiritBurstCountdownTest extends TestCase
{
    use RefreshDatabase;

    private TeamPanelBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = app(TeamPanelBuilder::class);
    }

    /**
     * @param  int|null  $normal  ordinary Spirit Bursts, or null when unrecorded
     * @param  int|null  $extreme  Extreme Spirit Bursts, or null when unrecorded
     * @return array<string, mixed>|null
     */
    private function countdown(?int $normal, ?int $extreme, string $scenario = 'unity_cup'): ?array
    {
        $run = TrainingRun::factory()->create([
            'scenario' => $scenario,
            'spirit_bursts_count' => $normal,
            'extreme_bursts_count' => $extreme,
        ]);

        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => 65,
        ]);

        return $this->builder->burstCountdown($run, $this->builder->teamSection($run));
    }

    public function test_reports_both_readings_of_the_burst_tally(): void
    {
        $countdown = $this->countdown(8, 3);

        $this->assertNotNull($countdown);
        $this->assertSame(8, $countdown['counts']['normal_only']['bursts']);
        $this->assertSame(11, $countdown['counts']['normal_and_extreme']['bursts']);
        // Each reading is priced against its own band, which is the whole point of carrying both.
        $this->assertSame('7–9', $countdown['counts']['normal_only']['band']);
        $this->assertSame('10–12', $countdown['counts']['normal_and_extreme']['band']);
    }

    public function test_names_which_reading_each_count_came_from_and_leaves_both_unconfirmed(): void
    {
        $countdown = $this->countdown(8, 3);

        $this->assertNotNull($countdown);
        // The combined reading is the one this build treats as active, and it says so rather than
        // presenting itself as settled. Neither basis string may claim the question is resolved.
        $this->assertSame(TeamPanelBuilder::BURST_MODE_COMBINED, $countdown['active_mode']);
        $this->assertStringContainsString('GameTora', $countdown['counts']['normal_and_extreme']['basis']);
        $this->assertStringContainsString('only', $countdown['counts']['normal_only']['basis']);
    }

    public function test_warns_with_the_combined_deficit_and_never_a_turn_count(): void
    {
        $countdown = $this->countdown(8, 3);

        $this->assertNotNull($countdown);
        $this->assertStringContainsString('Need 2 more bursts', $countdown['warning_message']);
        $this->assertStringContainsString('normal plus Extreme', $countdown['warning_message']);
        // The retired copy said "in 69 turns" off a turn 70 no source places. The timing key carries the
        // calendar vocabulary and the arithmetic key is an explicit null, not a guess.
        $this->assertStringNotContainsString('turn', $countdown['warning_message']);
        $this->assertNull($countdown['payout_turn']);
        $this->assertSame('Senior Year, Late November', $countdown['payout_timing']);
    }

    public function test_the_two_readings_disagree_across_a_band_boundary(): void
    {
        // 3 normal + 3 Extreme: combined 6 sits in the first band, normal-only 3 sits in none. A build
        // that folded one reading into the other would print a single band here and hide the gap.
        $countdown = $this->countdown(3, 3);

        $this->assertNotNull($countdown);
        $this->assertSame('4–6', $countdown['counts']['normal_and_extreme']['band']);
        $this->assertNull($countdown['counts']['normal_only']['band']);
        $this->assertNull($countdown['counts']['normal_only']['reward']);

        // Each reading walks to its own next edge: normal-only 3 must reach 4, combined 6 must reach 7.
        $this->assertSame(4, $countdown['counts']['normal_only']['next_threshold']);
        $this->assertSame(1, $countdown['counts']['normal_only']['bursts_needed']);
        $this->assertSame(7, $countdown['counts']['normal_and_extreme']['next_threshold']);
        $this->assertSame(1, $countdown['counts']['normal_and_extreme']['bursts_needed']);
    }

    public function test_both_readings_below_the_first_threshold_state_what_is_missing(): void
    {
        $countdown = $this->countdown(1, 1);

        $this->assertNotNull($countdown);
        $this->assertNull($countdown['counts']['normal_only']['band']);
        $this->assertNull($countdown['counts']['normal_and_extreme']['band']);
        // The floor is read from config (4), not from a literal in PHP.
        $this->assertSame(4, $countdown['counts']['normal_and_extreme']['next_threshold']);
        $this->assertSame(2, $countdown['counts']['normal_and_extreme']['bursts_needed']);
        $this->assertSame(3, $countdown['counts']['normal_only']['bursts_needed']);
    }

    public function test_places_each_reading_at_the_ten_to_twelve_and_thirteen_boundaries(): void
    {
        // 10 normal: Burning Spirit Lv1, and 3 more reach the top band.
        $ten = $this->countdown(10, 0);
        $this->assertNotNull($ten);
        $this->assertSame('10–12', $ten['counts']['normal_and_extreme']['band']);
        $this->assertStringContainsString('Burning Spirit Lv1', (string) $ten['counts']['normal_and_extreme']['reward']);
        $this->assertSame(13, $ten['counts']['normal_and_extreme']['next_threshold']);
        $this->assertSame(3, $ten['counts']['normal_and_extreme']['bursts_needed']);

        // 13 combined reaches the top band: no next threshold, so nothing is "needed".
        $thirteen = $this->countdown(10, 3);
        $this->assertNotNull($thirteen);
        $this->assertSame('13+', $thirteen['counts']['normal_and_extreme']['band']);
        $this->assertStringContainsString('Burning Spirit Lv3', (string) $thirteen['counts']['normal_and_extreme']['reward']);
        $this->assertNull($thirteen['counts']['normal_and_extreme']['next_threshold']);
        $this->assertNull($thirteen['counts']['normal_and_extreme']['bursts_needed']);
        $this->assertNull($thirteen['warning_message']);
    }

    public function test_labels_the_white_tier_ignited_spirit_and_the_gold_tier_burning_spirit(): void
    {
        $countdown = $this->countdown(5, 0);

        $this->assertNotNull($countdown);
        // The skill's own name is the label; "white hint" survives only as the tier description.
        $this->assertStringContainsString('Ignited Spirit Lv1', (string) $countdown['counts']['normal_only']['reward']);
        $this->assertStringContainsString('white hint', (string) $countdown['counts']['normal_only']['reward']);

        $gold = $this->countdown(11, 0);
        $this->assertNotNull($gold);
        $this->assertStringContainsString('Burning Spirit Lv1', (string) $gold['counts']['normal_only']['reward']);
        $this->assertStringContainsString('gold hint', (string) $gold['counts']['normal_only']['reward']);
    }

    public function test_a_missing_counter_is_not_a_zero(): void
    {
        // Extreme unrecorded: the combined reading cannot be answered, because adding an unrecorded
        // tally to a recorded one would invent the total. The normal-only reading still answers.
        $countdown = $this->countdown(8, null);

        $this->assertNotNull($countdown);
        $this->assertSame(8, $countdown['counts']['normal_only']['bursts']);
        $this->assertNull($countdown['counts']['normal_and_extreme']['bursts']);
        $this->assertNull($countdown['counts']['normal_and_extreme']['band']);
        $this->assertNull($countdown['warning_message']);
    }

    public function test_null_when_neither_counter_is_recorded(): void
    {
        $this->assertNull($this->countdown(null, null));
    }

    public function test_null_when_not_unity_cup(): void
    {
        $this->assertNull($this->countdown(8, 3, 'ura_finale'));
    }
}

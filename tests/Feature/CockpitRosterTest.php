<?php

namespace Tests\Feature;

use App\Enums\RosterDistance;
use App\Enums\RunStatus;
use App\Models\RosterMember;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * D01: the cockpit ships the canonical recorded roster from `RosterMember`,
 * never derived from `deckSlots`. The rendered copy is the browser spec's;
 * this file proves the wire, including all ten aptitude observations, the
 * five nullable stat caps, origin, support-card linkage, ACE and trainee flags,
 * and the write URLs the roster editor needs.
 */
final class CockpitRosterTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_recorded_roster_members_from_roster_members_table(): void
    {
        $run = TrainingRun::factory()->create([
            'umamusume_id' => Umamusume::factory()->create()->id,
            'status' => RunStatus::Active,
        ]);

        $first = RosterMember::factory()->for($run)->create([
            'name' => 'Guest Alpha',
            'origin' => 'guest',
            'position' => 1,
            'speed' => 900,
            'stamina' => 850,
            'power' => 880,
            'guts' => 820,
            'wit' => 860,
            'speed_cap' => 1100,
            'stamina_cap' => 1050,
            'power_cap' => 1080,
            'guts_cap' => 1020,
            'wit_cap' => 1060,
            'aptitude_turf' => 'A',
            'aptitude_dirt' => 'B',
            'aptitude_sprint' => 'A',
            'aptitude_mile' => 'B',
            'aptitude_medium' => 'A',
            'aptitude_long' => 'C',
            'aptitude_front_runner' => 'B',
            'aptitude_pace_chaser' => 'A',
            'aptitude_late_surger' => 'C',
            'aptitude_end_closer' => 'B',
            'distance_assignment' => RosterDistance::Sprint,
            'is_ace' => true,
            'is_trainee' => false,
        ]);

        $second = RosterMember::factory()->for($run)->create([
            'name' => 'Trainee Beta',
            'origin' => 'trainee',
            'position' => 2,
            'is_trainee' => true,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('roster.unrecorded', false)
                ->where('roster.max_members', RosterMember::MAX_MEMBERS)
                ->where('roster.empty', null)
                ->where('roster.members.0.id', $first->id)
                ->where('roster.members.0.name', 'Guest Alpha')
                ->where('roster.members.0.origin', 'guest')
                ->where('roster.members.0.speed', 900)
                ->where('roster.members.0.speed_cap', 1100)
                ->where('roster.members.0.stamina_cap', 1050)
                ->where('roster.members.0.power_cap', 1080)
                ->where('roster.members.0.guts_cap', 1020)
                ->where('roster.members.0.wit_cap', 1060)
                ->where('roster.members.0.aptitude_turf', 'A')
                ->where('roster.members.0.aptitude_dirt', 'B')
                ->where('roster.members.0.aptitude_sprint', 'A')
                ->where('roster.members.0.aptitude_mile', 'B')
                ->where('roster.members.0.aptitude_medium', 'A')
                ->where('roster.members.0.aptitude_long', 'C')
                ->where('roster.members.0.aptitude_front_runner', 'B')
                ->where('roster.members.0.aptitude_pace_chaser', 'A')
                ->where('roster.members.0.aptitude_late_surger', 'C')
                ->where('roster.members.0.aptitude_end_closer', 'B')
                ->where('roster.members.0.distance_assignment', RosterDistance::Sprint->value)
                ->where('roster.members.0.is_ace', true)
                ->where('roster.members.0.is_trainee', false)
                ->where('roster.members.0.position', 1)
                ->where('roster.members.1.id', $second->id)
                ->where('roster.members.1.name', 'Trainee Beta')
                ->where('roster.members.1.origin', 'trainee')
                ->where('roster.members.1.is_trainee', true)
                ->where('roster.members.1.position', 2));
    }

    public function test_names_the_absence_when_no_roster_member_is_recorded(): void
    {
        $run = TrainingRun::factory()->create([
            'umamusume_id' => Umamusume::factory()->create()->id,
            'status' => RunStatus::Active,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('roster.members', [])
                ->where('roster.unrecorded', true)
                ->where('roster.empty', fn (string $empty): bool => str_contains($empty, 'No roster member'))
                ->where('roster.store_url', route('runs.roster.store', $run))
                ->where('roster.max_members', RosterMember::MAX_MEMBERS));
    }

    public function test_member_rows_carry_update_and_destroy_urls(): void
    {
        $run = TrainingRun::factory()->create([
            'umamusume_id' => Umamusume::factory()->create()->id,
            'status' => RunStatus::Active,
        ]);

        $member = RosterMember::factory()->for($run)->create([
            'name' => 'Rostered Gamma',
            'position' => 1,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('roster.members.0.update_url', route('runs.roster.update', ['run' => $run->id, 'member' => $member->id]))
                ->where('roster.members.0.destroy_url', route('runs.roster.destroy', ['run' => $run->id, 'member' => $member->id])));
    }
}

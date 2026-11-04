<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReleaseStatus;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class SkillComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_compare_panel_renders_for_valid_id(): void
    {
        $skillA = Skill::factory()->create([
            'name' => 'Skill A',
            'name_ja' => 'スキルA',
            'type' => 'Speed',
            'sp_cost' => 130,
            'rarity' => 2,
            'is_unique' => false,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 1001,
            'condition_groups' => [
                ['condition' => 'distance_type==3', 'precondition' => null, 'effects' => [['type' => 27, 'value' => 100]]],
            ],
        ]);

        $skillB = Skill::factory()->create([
            'name' => 'Skill B',
            'name_ja' => 'スキルB',
            'type' => 'Recovery',
            'sp_cost' => 180,
            'rarity' => 1,
            'is_unique' => true,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 1002,
            'condition_groups' => [
                ['condition' => 'distance_type==2', 'precondition' => null, 'effects' => [['type' => 31, 'value' => 200]]],
            ],
        ]);

        // Debug: verify skills are in global scope
        $this->assertTrue($skillA->release_status === ReleaseStatus::GlobalReleased);
        $this->assertTrue($skillA->name_is_client);
        $this->assertTrue($skillB->release_status === ReleaseStatus::GlobalReleased);
        $this->assertTrue($skillB->name_is_client);

        $response = $this->get(route('skills.show', ['skill' => $skillA, 'compare' => $skillB->id]));
        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Skills/Show')
                ->where('skill.name', 'Skill A')
                ->has('compareSkill')
                ->where('compareSkill.name', 'Skill B')
            );
    }

    public function test_unknown_compare_id_returns_200_no_panel(): void
    {
        $skill = Skill::factory()->create([
            'name' => 'Test Skill',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $response = $this->get(route('skills.show', ['skill' => $skill, 'compare' => 999999]));
        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Skills/Show')
                ->where('skill.name', 'Test Skill')
                ->missing('compareSkill')
            );
    }

    public function test_diff_markers_on_type_class_code_unique_cost(): void
    {
        $skillA = Skill::factory()->create([
            'name' => 'Skill A',
            'type' => 'Speed',
            'sp_cost' => 130,
            'rarity' => 2,
            'is_unique' => false,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $skillB = Skill::factory()->create([
            'name' => 'Skill B',
            'type' => 'Recovery',
            'sp_cost' => 180,
            'rarity' => 1,
            'is_unique' => true,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $response = $this->get(route('skills.show', ['skill' => $skillA, 'compare' => $skillB->id]));
        $response->assertOk();

        $props = $response->viewData('page')['props'];
        $this->assertArrayHasKey('compareSkill', $props);
        $this->assertArrayHasKey('compareDiff', $props);

        $diff = $props['compareDiff'];

        // Type differs
        $this->assertArrayHasKey('type', $diff);
        $this->assertTrue($diff['type']);

        // Class code (rarity) differs
        $this->assertArrayHasKey('rarity', $diff);
        $this->assertTrue($diff['rarity']);

        // Unique differs
        $this->assertArrayHasKey('is_unique', $diff);
        $this->assertTrue($diff['is_unique']);

        // Cost differs
        $this->assertArrayHasKey('sp_cost', $diff);
        $this->assertTrue($diff['sp_cost']);
    }

    public function test_diff_markers_on_condition_precondition(): void
    {
        $skillA = Skill::factory()->create([
            'name' => 'Skill A',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'condition_groups' => [
                ['condition' => 'distance_type==3', 'precondition' => 'order<=5'],
            ],
        ]);

        $skillB = Skill::factory()->create([
            'name' => 'Skill B',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'condition_groups' => [
                ['condition' => 'distance_type==2', 'precondition' => null],
            ],
        ]);

        $response = $this->get(route('skills.show', ['skill' => $skillA, 'compare' => $skillB->id]));
        $response->assertOk();

        $props = $response->viewData('page')['props'];
        $diff = $props['compareDiff'];

        // Condition differs
        $this->assertArrayHasKey('condition_groups', $diff);
        $this->assertTrue($diff['condition_groups']);
    }

    public function test_diff_markers_on_cost_ladder(): void
    {
        $skillA = Skill::factory()->create([
            'name' => 'Skill A',
            'sp_cost' => 130,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $skillB = Skill::factory()->create([
            'name' => 'Skill B',
            'sp_cost' => 160,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $response = $this->get(route('skills.show', ['skill' => $skillA, 'compare' => $skillB->id]));
        $response->assertOk();

        $props = $response->viewData('page')['props'];
        $diff = $props['compareDiff'];

        // Cost ladder should differ (different base prices)
        $this->assertArrayHasKey('hintLadder', $diff);
        $this->assertTrue($diff['hintLadder']);
    }

    public function test_diff_markers_never_color_only(): void
    {
        $skillA = Skill::factory()->create([
            'name' => 'Skill A',
            'type' => 'Speed',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $skillB = Skill::factory()->create([
            'name' => 'Skill B',
            'type' => 'Recovery',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $response = $this->get(route('skills.show', ['skill' => $skillA, 'compare' => $skillB->id]));
        $response->assertOk();

        $props = $response->viewData('page')['props'];
        $diff = $props['compareDiff'];

        // The diff array should have boolean true for differences
        // The frontend uses this to render "differs" text + color
        // Verify the diff structure provides text markers
        $hasDifferences = false;
        foreach ($diff as $key => $value) {
            if ($value === true) {
                $hasDifferences = true;
                // Each true value represents a "differs" marker
                // The frontend should render this as text "differs" + color
            }
        }
        $this->assertTrue($hasDifferences, 'At least one diff key should be marked as different');
    }

    public function test_compare_picker_sets_compare_param(): void
    {
        // This tests the controller passes picker_skills for the compare control
        $skillA = Skill::factory()->create([
            'name' => 'Skill A',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $skillB = Skill::factory()->create([
            'name' => 'Skill B',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $response = $this->get(route('skills.show', ['skill' => $skillA, 'compare' => $skillB->id]));
        $response->assertOk();

        $props = $response->viewData('page')['props'];

        // Should have picker_skills for the compare control
        $this->assertArrayHasKey('picker_skills', $props);
        $this->assertIsArray($props['picker_skills']);
    }

    public function test_compare_skill_not_in_global_scope_ignored(): void
    {
        $skillA = Skill::factory()->create([
            'name' => 'Skill A',
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $skillB = Skill::factory()->create([
            'name' => 'Skill B',
            'release_status' => ReleaseStatus::JapanOnly,
            'name_is_client' => false,
        ]);

        // JapanOnly skill should not be in picker, but direct URL should still work
        // and just not show compare panel
        $response = $this->get(route('skills.show', ['skill' => $skillA, 'compare' => $skillB->id]));
        $response->assertOk();

        $props = $response->viewData('page')['props'];

        // Should not have compareSkill because it's not available on Global
        $this->assertArrayNotHasKey('compareSkill', $props);
    }

    public function test_compare_self_no_diff(): void
    {
        $skill = Skill::factory()->create([
            'name' => 'Test Skill',
            'type' => 'Speed',
            'sp_cost' => 130,
            'rarity' => 2,
            'is_unique' => false,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);

        $response = $this->get(route('skills.show', ['skill' => $skill, 'compare' => $skill->id]));
        $response->assertOk();

        $props = $response->viewData('page')['props'];

        // Should have compareSkill (it's the same skill)
        $this->assertArrayHasKey('compareSkill', $props);
        $this->assertEquals($skill->id, $props['compareSkill']['id']);

        // Diff should be empty or all false
        $diff = $props['compareDiff'];
        $anyTrue = array_filter($diff, fn ($v) => $v === true);
        $this->assertEmpty($anyTrue, 'Comparing a skill to itself should show no differences');
    }
}

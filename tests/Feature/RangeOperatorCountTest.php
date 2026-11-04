<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReleaseStatus;
use App\Http\Controllers\SkillController;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Range operator count test (B5 verification).
 *
 * Verifies that rangeOperatorCount() computes from actual data, not a hardcoded placeholder.
 * Uses a fresh DB seeded with one range-operator skill and one exact-token skill.
 */
final class RangeOperatorCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_range_operator_count_computes_from_data_nonzero(): void
    {
        // Seed a skill with a range operator on distance_type
        $rangeSkill = Skill::factory()->create([
            'name' => 'Range Operator Skill',
            'sp_cost' => 100,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 999901,
            'condition_groups' => [
                [
                    'condition' => 'distance_type<=3',
                    'precondition' => '',
                    'effects' => [['type' => 27, 'value' => 100]],
                    'base_time' => 50000,
                ],
            ],
        ]);

        // Seed a skill with exact token (should not count)
        $exactSkill = Skill::factory()->create([
            'name' => 'Exact Token Skill',
            'sp_cost' => 100,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 999902,
            'condition_groups' => [
                [
                    'condition' => 'distance_type==3',
                    'precondition' => '',
                    'effects' => [['type' => 27, 'value' => 100]],
                    'base_time' => 50000,
                ],
            ],
        ]);

        // Call the controller method directly via container
        $controller = app(SkillController::class);
        $count = $controller->rangeOperatorCount();

        // Only the range-operator skill should be counted
        $this->assertSame(1, $count, 'rangeOperatorCount should count only range operators');
    }

    public function test_range_operator_count_zero_when_none(): void
    {
        // Seed only exact-token skills
        Skill::factory()->create([
            'name' => 'Exact Only Skill 1',
            'sp_cost' => 100,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 999911,
            'condition_groups' => [
                [
                    'condition' => 'distance_type==3',
                    'precondition' => '',
                    'effects' => [['type' => 27, 'value' => 100]],
                    'base_time' => 50000,
                ],
            ],
        ]);

        Skill::factory()->create([
            'name' => 'Exact Only Skill 2',
            'sp_cost' => 100,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
            'export_id' => 999912,
            'condition_groups' => [
                [
                    'condition' => 'distance_type==2',
                    'precondition' => '',
                    'effects' => [['type' => 27, 'value' => 100]],
                    'base_time' => 50000,
                ],
            ],
        ]);

        $controller = app(SkillController::class);
        $count = $controller->rangeOperatorCount();

        $this->assertSame(0, $count, 'rangeOperatorCount should be 0 when no range operators exist');
    }
}

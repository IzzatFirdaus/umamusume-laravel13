<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\CardRarity;
use Tests\TestCase;

/**
 * Verify CardRarity level caps match UMAMUSUME_REFERENCE.md §1.4.2.
 *
 * SSR: unbroken 30, 4LB 50 (30 + 5×4)
 * SR:  unbroken 25, 4LB 45 (25 + 5×4)
 * R:   unbroken 20, 4LB 40 (20 + 5×4)
 */
final class CardRarityLevelCapsTest extends TestCase
{
    public function test_ssr_level_caps(): void
    {
        expect(CardRarity::ThreeStar->baseLevel())->toBe(30);
        expect(CardRarity::ThreeStar->levelAtBreak(0))->toBe(30);
        expect(CardRarity::ThreeStar->levelAtBreak(1))->toBe(35);
        expect(CardRarity::ThreeStar->levelAtBreak(2))->toBe(40);
        expect(CardRarity::ThreeStar->levelAtBreak(3))->toBe(45);
        expect(CardRarity::ThreeStar->levelAtBreak(4))->toBe(50);
    }

    public function test_sr_level_caps(): void
    {
        expect(CardRarity::TwoStar->baseLevel())->toBe(25);
        expect(CardRarity::TwoStar->levelAtBreak(0))->toBe(25);
        expect(CardRarity::TwoStar->levelAtBreak(1))->toBe(30);
        expect(CardRarity::TwoStar->levelAtBreak(2))->toBe(35);
        expect(CardRarity::TwoStar->levelAtBreak(3))->toBe(40);
        expect(CardRarity::TwoStar->levelAtBreak(4))->toBe(45);
    }

    public function test_r_level_caps(): void
    {
        expect(CardRarity::OneStar->baseLevel())->toBe(20);
        expect(CardRarity::OneStar->levelAtBreak(0))->toBe(20);
        expect(CardRarity::OneStar->levelAtBreak(1))->toBe(25);
        expect(CardRarity::OneStar->levelAtBreak(2))->toBe(30);
        expect(CardRarity::OneStar->levelAtBreak(3))->toBe(35);
        expect(CardRarity::OneStar->levelAtBreak(4))->toBe(40);
    }

    public function test_max_breaks_is_four(): void
    {
        expect(CardRarity::MAX_BREAKS)->toBe(4);
    }
}

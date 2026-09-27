<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Exact and Alias auto-promote; Fuzzy and None go to the review queue (PRD FR-B-3).
 */
enum MatchTier: string
{
    use HasLabel;

    case Exact = 'Exact';
    case Alias = 'Alias';
    case Fuzzy = 'Fuzzy';
    case None = 'None';
}

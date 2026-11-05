/**
 * Level → break-tier derivation, extracted from `DeckSummary.vue` (`tierWord`, `entries` computed).
 *
 * Single source for the five tier labels and the level → tier mapping. Used by
 * `DeckSummary.vue` and any future consumer.
 *
 * **No runtime game data.** The ladder is a published fact per rarity; this module only
 * translates a level number into the tier whose cap first holds it.
 */
export type BreakTierKey = 'unbroken' | 'break_1' | 'break_2' | 'break_3' | 'max_limit_break';

export interface BreakTierResult {
	/** Null when level is null — the absence is a fact, not a default. */
	tier: number | null;
	/** Exactly one of `BREAK_TIER_LABELS`. */
	label: string;
	/** True for the Max Limit Break tier. */
	isMaximum: boolean;
	/** The level cap for this tier (the published ceiling). */
	level: number;
}

/** The client's vocabulary, exactly as published. Single source for every UI label. */
export const BREAK_TIER_LABELS = [
	'Unbroken',
	'1 break',
	'2 breaks',
	'3 breaks',
	'Max Limit Break',
] as const;

/**
 * Map a card level to its break tier.
 *
 * The ladder has 5 tiers per rarity (unbroken = 0 breaks, then 1/2/3 breaks, then Max Limit Break).
 * A tier is the one whose `level` cap first holds the given level.
 * If level is null → `{ tier: null, label: 'Not recorded', isMaximum: false, level: 0 }`.
 * If level exceeds the top tier's cap → the max tier is returned (above-ladder case).
 */
export function breakTier(level: number | null, ladder: { breaks: number; is_maximum: boolean; level: number }[]): BreakTierResult {
	if (level === null) {
		return { tier: null, label: 'Not recorded', isMaximum: false, level: 0 };
	}

	const found = ladder.find((t) => level <= t.level);

	if (found) {
		const idx = ladder.indexOf(found);
		return {
			tier: idx,
			label: BREAK_TIER_LABELS[idx] ?? `${found.breaks} break${found.breaks === 1 ? '' : 's'}`,
			isMaximum: found.is_maximum,
			level: found.level,
		};
	}

	// Above the ladder — return the max tier
	const max = ladder[ladder.length - 1];
	const maxIdx = ladder.length - 1;
	return {
		tier: maxIdx,
		label: BREAK_TIER_LABELS[maxIdx] ?? `${max.breaks} break${max.breaks === 1 ? '' : 's'}`,
		isMaximum: true,
		level: max.level,
	};
}
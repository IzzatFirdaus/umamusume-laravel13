/**
 * Source-derived fixture for the Rice Shower Unity Cup regression suite.
 *
 * Every constant here is traceable to `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`. Values that the
 * report marks as unknown or disputed are typed as optional/conditional, and comments name the
 * relevant report section.
 */

export interface RiceShowerTrainee {
    /** Source: report §1.1 — Trainee identity. */
    name: string;
    /** Source: report §1.1 — card title, not asserted in the UI because the snapshot form does not collect it. */
    cardTitle: string;
}

export interface RiceShowerStats {
    /** Source: report §1.2 — observed live stats. */
    speed: number;
    stamina: number;
    power: number;
    guts: number;
    wit: number;
}

export interface RiceShowerStartingState {
    /** Source: report §1.1 — Scenario: Unity Cup. */
    scenario: string;
    /** Source: report §1.1 — position: Senior Year, Early October, 5 turns left. */
    careerYear: number;
    careerMonth: number;
    careerPhase: 'Early' | 'Late';
    /** Source: report §1.1 — Fans 209,245. */
    fans: number;
    /** Source: report §1.2 — unspent Skill Points 173. */
    skillPoints: number;
    stats: RiceShowerStats;
    /** Source: report §1.7 — Energy is not given as a number; the fixture leaves it unset. */
}

export interface SupportCardFixture {
    /** Source: report §1.4 — card display name (char + epithet). */
    displayName: string;
    /** Source: report §1.4 — discipline the card trains. */
    type: 'Speed' | 'Stamina' | 'Power' | 'Guts' | 'Wit' | 'Pal' | 'Group';
    /** Source: report §1.4 — observed client level / limit. */
    level: string;
    /** Stable primary key in the seeded catalogue (determined from `database/seeders/data/support-cards.json`). */
    id: number;
    /** Source: report §1.4 — whether the card is the borrowed friend card. */
    isFriend: boolean;
    /** Search string used in the deck picker to find this card. */
    searchQuery: string;
}

export interface SkillFixture {
    /** Source: report §1.3 — skill name as shown in the client. */
    name: string;
    /** Stable primary key in the seeded catalogue. */
    id: number;
}

export interface RiceShowerRace {
    /** Source: report §1.6 — recommended race. */
    title: string;
    /** Source: report §1.6 — grade. */
    tier: string;
    /** Source: report §1.6 — course facts. */
    course: string;
    /** Source: report §1.6 — fans on a win. */
    fansOnWin: number;
}

export const RICE_SHOWER: RiceShowerTrainee = {
    name: 'Rice Shower',
    cardTitle: '[Rosy Dreams]',
};

export const STARTING_STATE: RiceShowerStartingState = {
    scenario: 'unity_cup',
    careerYear: 3,
    careerMonth: 10,
    careerPhase: 'Early',
    fans: 209_245,
    skillPoints: 173,
    stats: {
        speed: 607,
        stamina: 577,
        power: 549,
        guts: 736,
        wit: 397,
    },
};

/**
 * Source: report §1.4 — the six support cards in the deck.
 * IDs were resolved from the seeded catalogue with `SELECT id, char_name, title_en FROM support_cards`.
 */
export const SUPPORT_DECK: SupportCardFixture[] = [
    { displayName: 'Tokai Teio [Dream Big!]', type: 'Speed', level: '35/35', id: 250, isFriend: false, searchQuery: 'Dream Big' },
    { displayName: 'Nishino Flower [Even the Littlest Bud]', type: 'Speed', level: '30/30', id: 265, isFriend: false, searchQuery: 'Littlest Bud' },
    { displayName: 'Biko Pegasus [Double Carrot Punch!]', type: 'Speed', level: '30/30', id: 267, isFriend: false, searchQuery: 'Double Carrot' },
    { displayName: 'Super Creek [Piece of Mind]', type: 'Stamina', level: '50/50', id: 263, isFriend: true, searchQuery: 'Piece of Mind' },
    { displayName: 'Matikanetannhauser [Just Keep Going]', type: 'Guts', level: '30/30', id: 277, isFriend: false, searchQuery: 'Just Keep Going' },
    { displayName: 'Haru Urara [Urara\'s Day Off!]', type: 'Guts', level: '35/35', id: 266, isFriend: false, searchQuery: "Urara's Day Off" },
];

/**
 * Source: report §1.3 — the owned skills visible on the Details / Learn screens.
 * IDs were resolved from the seeded catalogue with `SELECT id, name FROM skills`.
 */
export const OWNED_SKILLS: SkillFixture[] = [
    { name: 'Blue Rose Closer', id: 47 },
    { name: 'Red Shift/LP1211-M', id: 31 },
    { name: 'Corner Adept ○', id: 142 },
    { name: 'Swinging Maestro', id: 147 },
    { name: 'Nimble Navigator', id: 174 },
    { name: 'Steadfast', id: 217 },
    { name: 'Deep Breaths', id: 219 },
    { name: 'Extra Tank', id: 223 },
    { name: 'Pace Chaser Straightaways ○', id: 311 },
    { name: 'Shrewd Step', id: 315 },
    { name: 'Corner Recovery ○', id: 148 },
];

/**
 * Source: report §2.2 — purchasable skills mentioned for SP arithmetic checks.
 * The repository does not implement skill purchasing, but the fixture keeps the report's costs for
 * coverage arithmetic assertions where the Skills Planner can reach them.
 */
export const PURCHASABLE_SKILLS: Array<SkillFixture & { cost: number }> = [
    { name: 'Pace Chaser Straightaways ◎', id: 310, cost: 91 },
    { name: 'Kyoto Racecourse ○', id: -1, cost: 81 }, // not in fixture; used only for arithmetic note
    { name: 'Ignited Spirit GUTS', id: -1, cost: 130 }, // not in fixture; used only for arithmetic note
];

/**
 * Source: report §1.6 — the recommended race at the observed turn.
 */
export const KYOTO_DAISHOTEN: RiceShowerRace = {
    title: 'Kyoto Daishoten',
    tier: 'G2',
    course: 'Kyoto Turf 2400m (Medium) Right / Outer, Fall, Soft',
    fansOnWin: 6_700,
};

/**
 * Source: report §1.1 — active career goal.
 */
export const ACTIVE_GOAL = 'Place 1st in Arima Kinen';

/**
 * Source: report §1.8 — Unity Cup team facts that the repository cannot record.
 * The fixture keeps them so the test can assert the UI refuses to invent them.
 */
export const UNITY_CUP_TEAM_INFO = {
    team: 'Blue Bloom',
    motto: 'Dreaming Big',
    teamRank: 'S',
    leaguePlacement: 8,
    rankingBonus: 'All Attributes +30',
    statGrades: { speed: 'A', stamina: 'A', power: 'A', guts: 'S', wit: 'A' },
    unityTrainings: 55,
    spiritBursts: 6,
    extremeSpiritBursts: 5,
    members: '20/20',
};

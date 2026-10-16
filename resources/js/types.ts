// Inertia shared props, declared once so pages and the layout read them typed
// (ADR-0020 §1). Shape mirrors HandleInertiaRequests::share().
declare module '@inertiajs/core' {
    interface PageProps {
        app: {
            name: string;
            version: string | null;
            ruleset: string | null;
            /** The stored theme, or null for follow-the-OS. Mirrors `Preference::resolvedTheme()`. */
            theme: 'light' | 'dark' | null;
        };
        flash: {
            status: string | null;
        };
        preferences: {
            settings: Record<string, unknown> | null;
        };
        dashboard: {
            /** `config('dashboard.wide_bento_enabled')`: false until the DESIGN.md ruling lands. */
            wide_bento_enabled: boolean;
        };
    }
}

/*
 * The four provenance states, canonical here so a module TypeScript itself resolves can alias them:
 * `ProvenanceBadge.vue` re-exports the name, and an SFC aliases this type for every use that is not
 * inside its own `defineProps<>` (the compiler limit the badge's header documents). ProvenanceBadge.vue
 * remains the only owner of the glyphs and words the four states render.
 */
export type ProvenanceState = 'confirmed' | 'calculated' | 'estimated' | 'unknown';


/*
 * The shared Next Decision contract (plan §9a FD-2, PROMPT G1). Fixed here once because G2 to G6 all
 * consume it: FD-3 adds `objectives[]` and FD-4 adds `recorded_action` to this same shape, and the two
 * refusal classes, the provenance rows and the absence of any score are part of what they must not
 * redefine. Producer: `app/Services/Advisor/NextDecision.php`; consumer:
 * `resources/js/components/career/NextDecision.vue`, which re-declares the shape locally because
 * `defineProps<Imported>()` cannot expand an imported interface in this build (the header of
 * `ProvenanceBadge.vue` carries the measured boundary).
 *
 * **There is no score, no confidence and no band field, and that absence is the contract.** A score and
 * a numeric confidence are unsourced (`ADR-0001` §3) and `ADR-0020` §2 says no figure may read as more
 * certain than its provenance. `recommended` and `refusal` are mutually exclusive: when the engine
 * cannot rank, `recommended` is null and `refusal` names the inputs it had to work from.
 */
export interface Objective {
    label: string;
    /**
     * The four states FD-3 can reach from recorded data. `missed` is in the vocabulary the milestone
     * tracker will need (FD-5, G3) and is produced by nothing here: a slot behind the run with no entry is
     * the same stored fact as a slot not entered yet, and no source says it became unattainable.
     */
    state: 'upcoming' | 'current' | 'completed' | 'unknown';
    due_turn: number | null;
    due_label: string | null;
    /** Null because no source states what changes when an obligation is missed. Never an invented sentence. */
    consequence: string | null;
    /** Which read produced the row: `race_catalog_slots` today, so no row can name a scenario. */
    source: string;
    badge: ProvenanceState;
    title: string;
}

/**
 * G4's dual-value readout: one figure for what the advised session is worth to the trainee, one for
 * what it is worth to the team.
 *
 * `value` is pre-formatted server-side and null when the run records nothing to print; an empty row
 * carries no badge, because a badge qualifies a displayed value (`AGENTS.md` §5, owner ruling 2a and
 * the note on `why` below). `badge` is restricted to the two states this axis allows — `estimated`
 * would claim a model nothing builds, and `unknown` beside an absence was retired here — so a value of
 * `'confirmed' | 'calculated' | null` is the whole contract, not a subset of the four-state union.
 *
 * Declared here as the wire contract; the consumer declares it locally as well, because
 * `defineProps<Imported>()` cannot resolve an imported type in this build (plan §11).
 */
export interface DecisionValue {
    /** 'Trainee value' | 'Team value', composed server-side. */
    label: string;
    value: string | null;
    badge: 'confirmed' | 'calculated' | null;
    /** Where the figure comes from, and the reason printed beside an empty value. */
    title: string;
}

export interface NextDecision {
    /** One sentence naming which priority fired. Not a ranking of the seven §29 tiers. */
    priority: string;
    recommended: {
        action: string;
        action_url: string;
        /** What the action does for the run, in the engine's own words. Never a predicted yield. */
        benefit: string[];
        /**
         * G4's pair, present on every decision and null when the scenario composes no team ladder or
         * the advised action raises no stat. One shape rather than a key that sometimes is not there.
         */
        team_value: DecisionValue[] | null;
    } | null;
    alternatives: { action: string; action_url: string; tradeoff: string }[];
    /**
     * The provenance ledger, one row per figure the payload prints. Only `confirmed` (a declared
     * constant or a stored reading printed as read) and `calculated` (this tool's arithmetic over
     * readings) are legal on the advisor's axis — `estimated` would claim a model nothing builds, and
     * `unknown` beside an absence was retired by owner ruling 2a. The four-value union is declared
     * inline because `ProvenanceBadge.vue:12` owns it and no `.ts` in this build may import a type
     * from a `.vue` (there is no shim, and `npm run typecheck` fails on one).
     */
    why: {
        text: string;
        badge: ProvenanceState;
        title: string;
    }[];
    switch_if: string[];
    refusal: { reason: string; missing: string[] } | null;
    /**
     * FD-3's obligations, already ordered by `config('scenarios.objective_priority')`. The order is the
     * server's, and a consumer that re-sorted them would be ranking twice (ADR-0015).
     */
    objectives: Objective[];
    /**
     * FD-4's eighth key: the action last recorded, quoted from the snapshot stored when it was taken.
     *
     * Null for a run with no recorded action, and for every row written before the action noun existed.
     * Those rows hold no forecast, and a card that quoted a recomputed one would be printing advice no
     * screen ever showed the Trainer.
     */
    recorded_action: RecordedAction | null;
}

/**
 * The action vocabulary, mirroring `App\Enums\TurnChoice`.
 *
 * PHP owns the list; this is the client's copy of it, and `TurnChoiceTest`'s mirror case reads this file
 * and refuses the suite when the two disagree, which is the same pin `TurnEntryFieldSourceTest` puts on the
 * turn-entry field lists. A mirror nobody checks is a mirror that rots.
 */
export type TurnChoice =
    | 'training-Speed'
    | 'training-Stamina'
    | 'training-Power'
    | 'training-Guts'
    | 'training-Wit'
    | 'rest'
    | 'mood';

/**
 * One recorded action and its reconciliation (`plan §9a FD-4`).
 *
 * `decision` is the payload as it stood at the moment of the action and is never recomputed when the run
 * changes. `actual` is null until the Trainer enters the result, which is the difference between the
 * card's two shapes; `reconciliation` compares the action taken to the action recommended and never
 * compares gains, because the forecast names a deficit rather than a figure to hit.
 */
export interface RecordedAction {
    id: number;
    decision: Record<string, unknown>;
    /** The client's word for the action, printed from `App\Enums\TurnChoice::label()`. */
    action: string;
    /** The stored value, kept beside the word so a reader can trace it back to the enum. */
    choice: TurnChoice;
    expected: { text: string; badge: 'confirmed' | 'calculated'; title: string }[];
    actual: { text: string; badge: 'confirmed' | 'calculated'; title: string }[] | null;
    reconciliation: { match: boolean; note: string } | null;
    recorded_forecast: Record<string, unknown>;
    recorded_at: string | null;
    corrected_from: number | null;
}

/*
 * The scenario panel contract (plan §9 E1). Fixed once here because E2, E3, E4 and E6 all consume
 * it: each registers a renderer against a widget or flag key and reads this shape.
 *
 * A consuming SFC still declares its props locally — `@vue/compiler-sfc` cannot resolve an imported
 * type inside `defineProps` (TypeScript 7 ships no `lib/typescript.js`, plan §11) — so this is the
 * reasoning surface, not the props block.
 *
 * There is deliberately **no scenario identity field**: no key, id or config key. The section
 * carries `label` for display and the matrix's own widget and flag keys for the registry, and
 * nothing a component could branch on (gate G-33, `D-240`).
 */
export interface ScenarioTeamSection {
    /** The rank letter the Trainer most recently reported, or null while none is entered. */
    rank: string | null;
    /** Derived from the letter through the config ladder mapping; null above the top rung or unrecorded. */
    facility_level: number | null;
    /** The config ladder, flattened to one rung per letter. Empty when the flag is off. */
    ladder: { rank: string; level: number }[];
    /** One row per stat in config order. No column records a team stat grade, so grade is null. */
    stat_grades: { key: string; label: string; grade: string | null }[];
    /** The burst state each teammate was last recorded in, or empty. */
    roster: { teammate: string; state: string; stateLabel: string }[];
    /** The configured circle margin for team races, or null when the flag is off. */
    race_guidance: number | null;
    /** Recorded team race entries, title and tier carried as stored (nulls stay nulls). */
    race_entries: { title: string | null; tier: string | null; circles: number | null; placement: number | null }[];
    /** The config burst-count bands. Empty when the burst machine is not this scenario's. */
    bands: { total: string; reward: string }[];
    /** When the band payout lands, as config states it, or null. */
    payout_timing: string | null;
    /** The Special Training facts config states, each null where config says nothing. */
    special_training: { energy_penalty_removed: boolean | null; wit_burst_energy_bonus: number | null };
}

export interface ScenarioPanelSection {
    /** Display text only. Never compared, never a switch. */
    label: string;
    /** Whether the run names a scenario at all. Drives the empty state's wording, not a panel. */
    declared: boolean;
    /**
     * Whether the guide behind this scenario is complete, resolved by the controller from the
     * matrix's `partially_documented` marker (the owner's ruling of 2026-10-07, plan §4.1 item 6).
     * `true` means the badge's documented arm; absence of the marker in config means documented.
     */
    documented: boolean;
    /** Always null: `app.ruleset` is null and no source defines a Global ruleset version. */
    version: string | null;
    /** Why `version` is absent, for the `title`. */
    version_title: string;
    /**
     * The scenario's own Global ruleset date — its launch or rework instant, sourced per scenario —
     * or null when no scenario is set. A different fact from `version` above; the two render as
     * separate lines and must not merge (the R2-05 split).
     */
    ruleset_date: string | null;
    /** Where that date's event and source are stated, for the `title`. */
    ruleset_date_title: string;
    /** The scenario's own widget keys, labelled or not. A key with no label falls to the fallback. */
    widgets: string[];
    /** Display names, keyed by widget key. Only the keys the matrix labels appear. */
    widget_labels: Record<string, string>;
    /** Stored readings, keyed by widget key. Null until a column holds one. */
    widget_values: Record<string, number | string | null>;
    /** Why the widget readings are absent, for their `title`. Null when there are no widgets. */
    widgets_absence: string | null;
    /** Every panel flag the matrix declares, each with its own label and on/off state. */
    panels: Record<string, { label: string; on: boolean }>;
    /**
     * The team system's facts (E3, plan §9.1): what the run recorded through the team payloads and
     * what config states, under a key set that is the same for every scenario. A panel renderer
     * reads it; nothing in it names a scenario.
     */
    team: ScenarioTeamSection;
    objectives: unknown[];
    actions: unknown[];
    alerts: unknown[];
    recommendations: unknown[];
    /** Why no recommendation is offered, for the `title`. */
    recommendations_absence: string;
    /** The scenario's declared finale structure, or null. Not this run's state, which is `finale_state`. */
    finale_structure: unknown;
    /** Why no finale structure is shown, or null when one is declared. */
    finale_absence: string | null;
    /**
     * The Trackblazer-specific sections (plan §9 E4). Each is null for any scenario whose
     * matrix does not turn the matching flag on. A renderer reads these keyed by the matrix's
     * own flag name (`grade_objectives`, `shop`, `epithet_routes`), never by a scenario name.
     */
    grade: TrackblazerGradeSection | null;
    shop: TrackblazerShopSection | null;
    epithet: TrackblazerEpithetSection | null;
    /** Trackblazer rival races and the catalogue list. Null for any non-Trackblazer scenario. */
    rival: { rows: { title: string; year_label: string | null; turn: number | null; tier: string | null }[]; absence: string | null } | null;
    /**
     * The "Twinkle Star Climax" finale-name absence (§7 conflict row 31 UNVERIFIED). Null for
     * any non-Trackblazer scenario; a citation string for Trackblazer.
     */
    finale_official_title_absence: string | null;
    /**
     * The published stat caps, drawn by the baseline strip (plan §9 E6, D-241). Uniform for every
     * scenario; `cap` is the value `ScenarioCaps` states, and `base` and `bonus` travel separately
     * because the matrix forbids collapsing them.
     */
    caps: {
        verified_at: string;
        base: number;
        hard_cap: number;
        source_title: string;
        rows: { key: string; label: string; base: number; bonus: number; cap: number }[];
    };
    /**
     * The scenario's own sentence for why its panels render off, or null where its entry declares
     * none. Display copy from config, so a fifth scenario supplies its own line and no component
     * branches (gate G-33).
     */
    panel_absence: string | null;
    /** Where that reason is recorded, for the `title`. Null with `panel_absence`. */
    panel_absence_title: string | null;
}

/**
 * The Trackblazer grade-points meter (plan §9 E4, `x-grade-point-meter` provenance).
 * The ladder row count is four plus the debut row; the dirt-leaning and limited-turf-range
 * tracks the matrix also carries are not the rendered track (KI-15).
 */
export interface TrackblazerGradeSection {
    objectives: { index: number; name: string; required: number }[];
    current: number | null;
    earned: number | null;
    unpriced_count: number;
    unassigned_count: number;
    periods: { index: number; name: string; required: number; earned: number | null; unpriced: number }[];
    ladder_source: 'standard';
    rotation_turns: number | null;
    rotation_resets_in: number | null;
}

/**
 * The Trackblazer Pro Shop (plan §9 E4, `x-shop-panel` provenance).
 *
 * The catalogue is in `config('scenarios.scenarios.trackblazer.shop_items')` order, with no
 * `recommended` field, no "best value" sort or default selection. Purchases are events, not
 * a column (`ShopPurchasePayload` shape pinned in §3 + ADR-0003). The two action URLs are
 * the existing `runs.purchases.store` and `runs.update` routes — the boundary is
 * `StoreShopPurchaseRequest`.
 */
export interface TrackblazerShopSection {
    catalogue: { name: string; cost: number; effect: string }[];
    purchases: { item: string; cost: number; effect: string; turn: number }[];
    rotation_turns: number | null;
    rotation_resets_in: number | null;
    max_copies: number;
    spend_total: number;
    fill: string;
    select_action: string;
}

/**
 * The Trackblazer epithet checklist (plan §9 E4, `x-epithet-checklist` provenance). Three
 * states per row, with `unverifiable` the load-bearing distinction (D-220, D-256).
 */
export interface TrackblazerEpithetSection {
    rows: { route: string; epithet: string; reward: string; state: 'earned' | 'open' | 'unverifiable'; missing: string[]; note: string | null }[];
    seen_titles: string[];
}

export {};

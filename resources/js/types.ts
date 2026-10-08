// Inertia shared props, declared once so pages and the layout read them typed
// (ADR-0020 §1). Shape mirrors HandleInertiaRequests::share().
declare module '@inertiajs/core' {
    interface PageProps {
        app: {
            name: string;
            version: string | null;
            ruleset: string | null;
        };
        flash: {
            status: string | null;
        };
    }
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
    /** The scenario's declared finale structure, or null. */
    finale: unknown;
    /** Why no finale is shown, or null when one is declared. */
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

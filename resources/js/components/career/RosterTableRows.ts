/*
 * The roster row shape, shared by the team workspace's reader and its table.
 *
 * `CockpitController::rosterSectionWithUrls()` is the one producer, so this type is where the
 * frontend's read contract with that builder lives. Kept out of the `.vue` files because a type
 * exported from `<script setup>` is invisible to `tsc --noEmit` (`DataTable`'s note records the same
 * reason for `data-table-types.ts`).
 */
export interface RosterMemberRow {
    id: number;
    name: string;
    grade: string | null;
    speed: number | null;
    stamina: number | null;
    power: number | null;
    guts: number | null;
    wit: number | null;
    /**
     * The ceiling the client prints beside each stat (`§1.9`), read with the stat so a member who is
     * AT her ceiling is distinguishable from one with room left. Null is "not recorded", never "no
     * cap" (`D-220`) — the whole point of the pair is that a bare `1200` says nothing about headroom.
     */
    speed_cap: number | null;
    stamina_cap: number | null;
    power_cap: number | null;
    guts_cap: number | null;
    wit_cap: number | null;
    /**
     * The origin of this member (UC3.2). One of 'trainee', 'support', 'guest', 'unknown'.
     * 'unknown' is the honest arm for a member the Trainer added by name without recording
     * where she came from — the default, because an origin nobody observed is an absence
     * (D-220), not a guess.
     */
    origin: string | null;
    /**
     * The exact support card a support-derived member joined through (UC3.2). FK points at
     * `support_cards.id`; null where the member is the trainee, a guest, or no card is equipped.
     */
    support_card_id: number | null;
    /**
     * The ten observed aptitude letters (UC3.4). Null = unobserved, not absent. The letter
     * vocabulary is the client's own, the same one `grade` uses. Catalogue values (source-published)
     * are a distinct fact from observed in-run values — §1.2's erratum D8 is the standing example
     * of the two disagreeing without either side being wrong.
     */
    aptitude_turf: string | null;
    aptitude_dirt: string | null;
    aptitude_sprint: string | null;
    aptitude_mile: string | null;
    aptitude_medium: string | null;
    aptitude_long: string | null;
    aptitude_front_runner: string | null;
    aptitude_pace_chaser: string | null;
    aptitude_late_surger: string | null;
    aptitude_end_closer: string | null;
    /**
     * The ceiling the client prints beside each stat (`§1.9`), read with the stat so a member who is
     * AT her ceiling is distinguishable from one with room left. Null is "not recorded", never "no
     * cap" (`D-220`) — the whole point of the pair is that a bare `1200` says nothing about headroom.
     */
    distance_assignment: string | null;
    is_ace: boolean;
    is_trainee: boolean;
    umamusume_id: number | null;
    position: number;
    update_url: string;
    destroy_url: string;
}

export interface RosterPayload {
    members: RosterMemberRow[];
    empty: string | null;
    unrecorded: boolean;
    store_url: string;
    /** `RosterMember::MAX_MEMBERS` (20), the client's own ceiling (§1.8). */
    max_members: number;
}

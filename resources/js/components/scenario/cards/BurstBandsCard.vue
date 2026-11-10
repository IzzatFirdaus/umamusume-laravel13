<script setup lang="ts">
/*
 * One card: the burst-count band table, the roster that names each teammate's burst state, and the
 * publisher conflicts those figures rest on.
 *
 * Extracted from `UnityCupPanel.vue` for `S-RS-VISUAL-1`. Move 3 of the consolidation plan
 * (`28d9afbf`, owner Ruling 2) had already merged `SpiritBurstRoster` into this card as a labelled
 * sub-block, because the two panels state one run's burst position and side by side they left
 * 236 px of dead column (`GRID-REVIEW.md` §P4). The merge is kept; this extraction moves the card
 * out of the panel's own grid so the page can place it, and applies W.1 = C's plain `h2`.
 *
 * The `Estimated` badge stays on the bands half only, because it marks that figure's provenance and
 * not the roster's. The provenance badge's `title` is carried over verbatim.
 */
import AlertRow from '../AlertRow.vue';
import ProvenanceBadge from '../../ProvenanceBadge.vue';
import SpiritBurstRoster from '../../SpiritBurstRoster.vue';

type BurstState =
    | 'Chargeable'
    | 'Charged'
    | 'Held'
    | 'NormalBurstSpent'
    | 'ExtremeChargeable'
    | 'ExtremeSpent';

defineProps<{
    bands: { total: string; reward: string }[];
    burstBand: string | null;
    combinedBursts: number | null;
    bandsSource: string | null;
    bandsCaveat: string | null;
    payoutTiming: string | null;
    roster: { teammate: string; state: BurstState; stateLabel: string }[];
    burstsEnabled: boolean;
}>();
</script>

<template>
    <div class="card p-3">
        <div class="flex flex-wrap items-baseline gap-2">
            <h2 class="text-lg font-semibold text-ink-strong">Burst count bands</h2>
            <ProvenanceBadge
                state="estimated"
                title="Transcribed from the scenario guide's post-rework table, which rests on one publisher where two report different Skill Point figures, and the guide's own line that Extreme Bursts count toward the total is a live disagreement."
            />
        </div>

        <table class="mt-2 w-full text-sm">
            <caption class="sr-only">
                Reward by combined Spirit Burst and Extreme Spirit Burst count
            </caption>
            <thead>
                <tr class="border-b border-rule text-left text-xs text-ink-muted">
                    <th scope="col" class="py-1 font-semibold">Total bursts</th>
                    <th scope="col" class="py-1 font-semibold">Reward</th>
                </tr>
            </thead>
            <tbody>
                <!-- The band the run's combined total falls in is marked, so the table reads
                     the run's own position rather than only the reward reference. -->
                <tr
                    v-for="band in bands"
                    :key="band.total"
                    class="border-b border-rule last:border-b-0"
                    :class="band.total === burstBand ? 'bg-raised' : ''"
                >
                    <th scope="row" class="py-1.5 pr-2 text-left font-mono font-normal tabular-nums text-ink">
                        {{ band.total }}
                        <span v-if="band.total === burstBand" class="ml-1 text-xs font-semibold text-ink-strong">(this run)</span>
                    </th>
                    <td class="py-1.5 text-ink">{{ band.reward }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Provenance, kept out of the shipped detail string because a section reference is opaque
             to a Trainer. The exact anchor lives here instead, which is where a maintainer re-checks
             the claim: `UMAMUSUME_REFERENCE.md` §2.2.2, conflict row 57 — the client's own Team Info
             panel is the tiebreaker for this table's edge-versus-criterion disagreement. The shipped
             detail names the source by content for the same reason `TrackblazerPanel.vue` keeps its
             §2.2.3 anchor in a comment rather than the copy. -->
        <AlertRow
            class="mt-2"
            glyph="⚠"
            text="Three parts of this table are publisher conflicts, not settled facts: whether Extreme Bursts count toward the total, where the low bands' edges fall, and the month of the payout event."
            detail="gametora prints four numbered bands (4 to 9, 7 to 9, 10 to 12, 13+) paying 10/20/30/40 SP with the matching stat, and notes that its own low-end values are not known; umamusu.wiki prints the same four reward tiers but attaches a criterion to the gold rows rather than numbered edges, and never says whether Extremes increment. The two disagree about edges versus criteria, not about the Skill Point figures. Gametora's first two bands (4 to 9 and 7 to 9) overlap and cannot both hold, so the edges this table prints are the scenario settings' resolution of that conflict, not a publisher's. The payout is dated Late November where those publishers print the first half. The client's own Team Info count is the tiebreaker."
            tone="note"
        />

        <!-- The carrier and the low-band caveat. The figures were printed with neither, so a
             reader could not tell whose bands these are or that the published sets disagree at the
             bottom of the table. Both arrive from the payload rather than being paraphrased here. -->
        <p v-if="bandsSource !== null" class="mt-2 text-xs text-ink-muted">
            Source: {{ bandsSource }}
        </p>
        <p v-if="bandsCaveat !== null" class="mt-1 text-xs text-ink-muted">
            {{ bandsCaveat }}
        </p>
        <p class="mt-2 text-xs text-ink-muted">
            <template v-if="payoutTiming !== null">
                The gold versions arrive from the scripted event in {{ payoutTiming }}.
            </template>
            <!-- The run's own position, read off the combined Spirit + Extreme total the
                 counters carry. Until both tallies are recorded there is no combined count to
                 place, so the absence is stated rather than a band guessed. -->
            <template v-if="combinedBursts !== null">
                This run: {{ combinedBursts }} bursts combined<template v-if="burstBand !== null">, inside the {{ burstBand }} band</template>.
            </template>
            <template v-else>
                Where this run sits in the bands is not shown: both burst tallies have not been recorded.
            </template>
        </p>

        <!-- Move 3 (`28d9afbf`, Ruling 2): the roster joins this card as a labelled sub-block. The
             two panels state one run's burst position and left 236 px of dead column when they stood
             side by side; the divider keeps them readable as two things while the card, its header
             and its provenance badge stay single. -->
        <div class="mt-3 border-t border-rule pt-3">
            <SpiritBurstRoster
                :enabled="burstsEnabled"
                :roster="roster"
                bare
            />
        </div>
    </div>
</template>

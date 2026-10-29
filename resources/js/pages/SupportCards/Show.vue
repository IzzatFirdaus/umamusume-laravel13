<script setup lang="ts">
/*
 * One support card, rebuilt to `support-card-detail.html` (plan Slice 8).
 *
 * The stage table is the card: an effect's value is a function of the card's level, so each row lists
 * the levels the source states a value at, and the last of them is the cap. A level between two
 * anchors carries no entry, which is why the screen cannot draw one column per break: a gap is an
 * absence, not a zero, and an interpolated cell would be a figure the source never stated.
 *
 * The break widget is the client's own grammar (four diamonds and a level readout), with the levels it
 * would read filled per completed break. This catalogue stores no owned copies, so the diamonds render
 * empty and the readout says so; the run-scoped levels appear in Where this card is used, where each
 * run's own slot carries the level it recorded.
 *
 * Provenance is a row plus a link to the ledger. No file path and no snapshot location reaches this
 * screen; the source URL stays inside the disclosure that holds the fetch history.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import RarityChip from '../../components/RarityChip.vue';
import SkillRow from '../../components/SkillRow.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import SupportTypeMark from '../../components/support/SupportTypeMark.vue';
import { LinkButton } from '../../components/Form';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Effect {
    effect_id: number;
    name: string | null;
    display: string;
}

interface StageAnchor {
    level: number;
    display: string;
}

interface EffectStage {
    effect_id: number;
    name: string | null;
    anchors: StageAnchor[];
    cap: StageAnchor;
}

interface Skill {
    name: string;
    sp_cost: number | null;
    is_unique: boolean;
    url: string;
}

interface SkillList {
    stored: boolean;
    skills: Skill[];
    unlinked: number;
}

interface UsedBy {
    run_id: number;
    trainee_name: string;
    scenario_label: string;
    status_label: string;
    card_level: number | null;
    url: string;
}

interface Card {
    id: number;
    name: string;
    /** The header thumbnail's loopback URL, keyed on the publisher's `support_id`. Null when unmirrored. */
    artworkURL: string | null;
    name_ja: string | null;
    title_ja: string | null;
    rarity_label: string;
    rarity_stars: string;
    rarity_word: string;
    type: string;
    type_label: string;
    release_status: string;
    release_jp_display: string | null;
    release_global_display: string | null;
    char_name: string | null;
    source_url: string | null;
    fetched_at_display: string | null;
    is_manual: boolean;
}

const props = defineProps<{
    card: Card;
    effects: Effect[];
    effectStages: EffectStage[];
    /** The level a card of this rarity reaches at four breaks (50 / 45 / 40). */
    levelCap: number;
    usedBy: UsedBy[];
    hinted: SkillList;
    events: SkillList;
    trainee: { name: string; url: string } | null;
}>();

// The source publishes the Japanese name and epithet as their own fields, so both print when both
// exist. A Pal card carries no Japanese name at all, and an empty line would read as a rendering
// accident rather than as an absence.
const hasJapanese = computed(() => props.card.name_ja !== null || props.card.title_ja !== null);
const japaneseName = computed(() => `${props.card.name_ja ?? ''} ${props.card.title_ja ?? ''}`.trim());

// Both lists are the source's own ids, kept in the order it publishes them, because a card's hint
// list is the order the game prints it in.
const skillSections = computed(() => [
    { heading: 'Hinted skills', noun: 'hinted', data: props.hinted },
    { heading: 'Event skills', noun: 'event', data: props.events },
]);

// Four breaks, one diamond each, read left to right. `filled` is the number of completed breaks, and
// this catalogue holds none: the client's pattern is drawn at its unbroken state rather than omitted,
// so a Trainer sees what the widget will read once they record their copy.
const BREAKS = 4;

const th = 'border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong';
const thNum = 'border-b border-rule px-2 py-2 text-right text-xs font-semibold text-ink-strong';
const td = 'border-b border-rule px-2 py-2 align-top text-sm text-ink';
const tdNum = 'border-b border-rule px-2 py-2 text-right align-top font-mono text-sm text-ink';
const tdMono = 'border-b border-rule px-2 py-2 align-top font-mono text-xs text-ink';
const rowLabel = 'w-1/3 px-2 py-2 align-top text-sm text-ink-muted';
const rowValue = 'px-2 py-2 align-top text-sm text-ink';

const statedAt = (stage: EffectStage): string =>
    stage.anchors.map((anchor) => `${anchor.level}: ${anchor.display}`).join(' · ');
</script>

<template>
    <AppLayout
        :breadcrumb="[
            { label: 'Support cards', href: '/support-cards' },
            { label: props.card.name },
        ]"
    >
        <Head :title="props.card.name" />
        <template #head>
            <PageHeader eyebrow="Support card" :title="props.card.name">
                <template #actions>
                    <LinkButton href="/support-cards" variant="secondary">Back to cards</LinkButton>
                    <!--
                        "Add to a deck" (`support-card-detail.html:66`).

                        A deck belongs to a run or to a setup draft, never to the catalogue, so this
                        opens the one deck surface that needs no existing context: `career/setup/deck`
                        renders its six empty slots when no draft exists (`DeckSelectController:66`,
                        `SetupDraft::deck() ?? $this->emptySlots()`) rather than redirecting. That is
                        why the link is honest from here — it lands somewhere the Trainer can act,
                        not on an error.

                        It is deliberately the only filled action on the page.
                    -->
                    <LinkButton href="/career/setup/deck" variant="primary">Add to a deck</LinkButton>
                </template>
            </PageHeader>
        </template>

        <DatabaseNav />

        <div v-if="props.card.artworkURL || hasJapanese" class="mb-4 flex items-center gap-3">
            <!-- No link: this page is the card, so a frame around it would be a link to itself
                 (`DESIGN.md` §4.7, the header slot's no-action case). Decorative alt, because the name
                 prints beside it. -->
            <ArtworkSlot :url="props.card.artworkURL" alt="" size="size-16" />
            <p v-if="hasJapanese" lang="ja" class="text-sm text-ink-muted">{{ japaneseName }}</p>
        </div>

        <div class="grid gap-4 g-side">
            <div>
                <div class="card">
                    <h2>Effects, as the source states them</h2>
                    <p class="sub">Each row lists the card levels the source gives a value for, and the last of them is the cap. A level between two stated anchors carries no entry, which is not the same thing as a zero.</p>
                    <p v-if="props.effectStages.length === 0" class="text-sm text-ink-muted">
                        The source states no effect anchor for this card.
                    </p>
                    <template v-else>
                        <table class="w-full border-collapse">
                            <thead>
                                <tr>
                                    <th :class="th">Effect</th>
                                    <th :class="th">Stated at</th>
                                    <th :class="thNum">Cap</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="stage in props.effectStages" :key="stage.effect_id">
                                    <td :class="td">{{ stage.name ?? `Unnamed effect ${stage.effect_id}` }}</td>
                                    <td :class="tdMono">{{ statedAt(stage) }}</td>
                                    <td :class="tdNum">
                                        <b>{{ stage.cap.display }}</b> · Lv {{ stage.cap.level }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="note mt-3">
                            <span class="g" aria-hidden="true">○</span>
                            <span>
                                These are this card's own figures, not the ladder's shape. Every figure is
                                a stated anchor and the highest stated anchor is the cap, never an
                                interpolation. The client's cap for a {{ props.card.rarity_word }} is
                                {{ props.levelCap }}: each break adds five levels, and four breaks are the
                                maximum.
                            </span>
                        </p>
                    </template>
                </div>

                <section
                    v-for="list in skillSections"
                    :key="list.noun"
                    :aria-labelledby="`${list.noun}-skills`"
                >
                    <div class="card mt-4">
                        <h2 :id="`${list.noun}-skills`">{{ list.heading }}</h2>
                        <p v-if="!list.data.stored" class="text-sm text-ink-muted">
                            No {{ list.noun }} skill list is stored for this card.
                        </p>
                        <p v-else-if="list.data.skills.length === 0" class="text-sm text-ink-muted">
                            The source lists no {{ list.noun }} skills for this card.
                        </p>
                        <ul v-else>
                            <SkillRow v-for="skill in list.data.skills" :key="skill.url" :skill="skill" />
                        </ul>

                        <p v-if="list.data.unlinked > 0" class="note mt-3">
                            <span class="g" aria-hidden="true">○</span>
                            <span>
                                {{ list.data.unlinked }} {{ list.noun }} skill ids in the source's list resolve
                                to no skill page this catalog can open, so they are counted here rather than
                                linked or dropped.
                            </span>
                        </p>
                    </div>
                </section>
            </div>

            <div>
                <div class="card">
                    <h2>Card</h2>
                    <div class="grid gap-2 g-2">
                        <div class="tile">
                            <div class="k">Rarity</div>
                            <div class="v flex items-baseline gap-2">
                                <RarityChip :label="props.card.rarity_label" :stars="props.card.rarity_stars" />
                                {{ props.card.rarity_word }}
                            </div>
                        </div>
                        <div class="tile">
                            <div class="k">Type</div>
                            <div class="v">
                                <SupportTypeMark :type="props.card.type" :label="props.card.type_label" />
                            </div>
                        </div>
                        <div class="tile">
                            <div class="k">Availability</div>
                            <div class="v">{{ props.card.release_status }}</div>
                        </div>
                        <div class="tile">
                            <div class="k">Level cap</div>
                            <div class="v font-mono">{{ props.levelCap }}</div>
                        </div>
                    </div>

                    <table class="mt-3 w-full border-collapse">
                        <tbody>
                            <tr>
                                <th scope="row" :class="rowLabel">Released in Japan</th>
                                <td :class="rowValue">{{ props.card.release_jp_display ?? 'Not recorded' }}</td>
                            </tr>
                            <tr>
                                <th scope="row" :class="rowLabel">Released on Global</th>
                                <td :class="rowValue">{{ props.card.release_global_display ?? 'Not recorded' }}</td>
                            </tr>
                            <tr>
                                <th scope="row" :class="rowLabel">Trainee</th>
                                <td :class="rowValue">
                                    <Link v-if="props.trainee" :href="props.trainee.url" class="text-ink-strong underline">
                                        {{ props.trainee.name }}
                                    </Link>
                                    <!-- `char_id` addresses the source's whole character space, not this
                                         catalog's, so the join misses two ways: the 9000-block staff have
                                         no trainee row at all, and a card can name a trainee this catalog
                                         does not track. -->
                                    <span v-else>
                                        {{ props.card.char_name ?? 'This card' }} has no trainee page in this catalog.
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="mt-4 text-xs font-semibold text-ink-muted">Limit break</p>
                    <p class="mt-1 text-sm text-ink">
                        Four breaks, consuming copies of the same card: a fully broken card is five copies.
                        Two identical copies <b>cannot sit in one deck</b>, which is why duplicates are
                        spent instead of equipped.
                    </p>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                        <span class="flex gap-1" aria-hidden="true">
                            <span v-for="slot in BREAKS" :key="slot" class="font-mono text-ink-faint">◇</span>
                        </span>
                        <span class="font-semibold text-ink-strong">breaks not recorded</span>
                        <span class="ml-auto font-mono text-xs text-ink-muted">cap {{ props.levelCap }}</span>
                    </div>
                    <p class="mt-1 text-xs text-ink-muted">
                        Four diamonds, one per break, read left to right. This catalogue does not store
                        owned copies, so the readout states the absence; a run's own deck records the level
                        it equipped.
                    </p>
                </div>

                <div class="card">
                    <h2>Where this card is used</h2>
                    <!-- The wireframe's empty state is a NAMED ROW with a neutral pill
                         (`support-card-detail.html:170`), not a bare sentence. A card no run holds is
                         a normal state rather than a warning, so it reads as a labelled row carrying
                         the neutral pill the rest of the app uses for "known and unremarkable". -->
                    <table v-if="props.usedBy.length === 0" class="w-full border-collapse">
                        <tbody>
                            <tr>
                                <td :class="td">This card is in no run's deck.</td>
                                <td :class="[td, 'text-right']">
                                    <span class="pill pill-neutral">Not used</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table v-else class="w-full border-collapse">
                        <tbody>
                            <tr v-for="run in props.usedBy" :key="run.run_id">
                                <td :class="td">
                                    <Link :href="run.url" class="text-ink-strong underline">
                                        {{ run.trainee_name }} · {{ run.scenario_label }}
                                    </Link>
                                    <span class="block text-xs text-ink-muted">
                                        {{ run.status_label }} ·
                                        <template v-if="run.card_level !== null">equipped at level {{ run.card_level }}</template>
                                        <template v-else>level not recorded for this run</template>
                                    </span>
                                </td>
                                <td :class="[td, 'text-right']">
                                    <span class="pill pill-positive">In deck</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="mt-2 text-xs text-ink-muted">
                        Runs store their own six slots, so one card can appear in several runs without conflict.
                    </p>
                </div>

                <div class="card">
                    <h2>Provenance</h2>
                    <table class="w-full border-collapse">
                        <tbody>
                            <tr>
                                <th scope="row" :class="rowLabel">Data</th>
                                <td :class="rowValue">
                                    <template v-if="props.card.source_url">GameTora support-card export</template>
                                    <span v-else>No fetched source; this record was seeded or entered by hand.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" :class="rowLabel">Last read</th>
                                <td :class="rowValue">{{ props.card.fetched_at_display ?? 'Not recorded' }}</td>
                            </tr>
                            <tr>
                                <th scope="row" :class="rowLabel">Corrected by hand</th>
                                <td :class="rowValue">
                                    {{ props.card.is_manual ? 'Yes, and the fetch engine leaves this row alone' : 'No' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-3">
                        <LinkButton href="/database/sources" variant="secondary" size="sm">Open the source ledger</LinkButton>
                    </div>

                    <!-- ONE disclosure for the row's own provenance. The heading stays on screen so the
                         section is findable, and the summary names which state the row is in before the
                         disclosure is opened. -->
                    <details id="provenance" class="mt-3">
                        <summary class="min-h-11 cursor-pointer list-item text-sm font-semibold text-ink-strong">
                            <template v-if="props.card.source_url">Fetched source</template>
                            <template v-else>No fetched source</template>
                        </summary>
                        <p v-if="props.card.source_url" class="mt-2 text-xs text-ink-muted">
                            <a :href="props.card.source_url" class="break-all text-ink-strong underline">{{ props.card.source_url }}</a>
                            <template v-if="props.card.fetched_at_display"> · read {{ props.card.fetched_at_display }}</template>
                        </p>
                        <p v-else class="mt-2 text-sm text-ink-muted">This record was seeded or entered by hand.</p>
                    </details>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

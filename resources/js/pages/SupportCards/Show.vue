<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import RarityChip from '../../components/RarityChip.vue';
import SkillRow from '../../components/SkillRow.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Effect {
    effect_id: number;
    name: string | null;
    display: string;
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
    { heading: 'Hinted', noun: 'hinted', data: props.hinted },
    { heading: 'Event', noun: 'event', data: props.events },
]);
</script>

<template>
    <AppLayout>
        <Head :title="card.name" />
        <template #title>{{ card.name }}</template>

        <div class="flex justify-end">
            <Link href="/support-cards" class="text-sm text-ink-muted hover:underline">Back to support cards</Link>
        </div>

        <!-- The header thumbnail (`ADR-0021` read half, `design-2.0` §45a "Support-card detail,
             header"), at the recorded `size-16`, leading the block that names this card.

             No `href`, because §45a gives this slot the click action *no action* and the component
             then renders no anchor at all. The Blade component this replaces always emitted one, so
             on a detail page it produced a link to the page already open — and its `decorative` flag
             had blanked that link's `aria-label`, making it a focusable control with no accessible
             name, which fails WCAG 2.2 AA 4.1.2. Splitting the alt decision from the link decision
             is what lets this screen take the no-action case honestly.

             Decorative `alt=""`: the shell banner's `<h1>` is this card's name, so the name is read
             once, from the text. Renders nothing when the mirror holds no file, leaving the block
             text-only with no placeholder (`DESIGN.md` §4.7).

             The wrapper is guarded on `card.artworkURL || hasJapanese` so a card with neither a
             mirrored file nor a Japanese name renders no empty row: the frame and the Japanese line
             both live inside it, and a `mt-4` flex box holding nothing would otherwise leave a band
             of dead space above the field grid. Its `mt-4` is the spacing this block now needs to
             carry a 64px frame, which the old text-only `mt-1` line did not. -->
        <div v-if="card.artworkURL || hasJapanese" class="mt-4 flex items-center gap-3">
            <ArtworkSlot :url="card.artworkURL" alt="" size="size-16" />
            <p v-if="hasJapanese" lang="ja" class="text-sm text-ink-muted">{{ japaneseName }}</p>
        </div>

        <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-3">
            <div>
                <dt class="text-ink-muted">Rarity</dt>
                <dd class="flex items-baseline gap-2 text-ink">
                    <RarityChip :label="card.rarity_label" :stars="card.rarity_stars" />
                    {{ card.rarity_word }}
                </dd>
            </div>

            <div>
                <dt class="text-ink-muted">Type</dt>
                <dd class="text-ink">{{ card.type_label }}</dd>
            </div>

            <div>
                <dt class="text-ink-muted">Availability</dt>
                <dd class="text-ink">{{ card.release_status }}</dd>
            </div>

            <div>
                <dt class="text-ink-muted">Released in Japan</dt>
                <dd class="text-ink">
                    <template v-if="card.release_jp_display !== null">{{ card.release_jp_display }}</template>
                    <span v-else title="The source states no Japan release date for this card.">N/A</span>
                </dd>
            </div>

            <div>
                <dt class="text-ink-muted">Released on [Global]</dt>
                <dd class="text-ink">
                    <template v-if="card.release_global_display !== null">{{ card.release_global_display }}</template>
                    <span v-else title="The source states no Global release date for this card.">N/A</span>
                </dd>
            </div>

            <div>
                <dt class="text-ink-muted">Belongs to</dt>
                <dd class="text-ink">
                    <a v-if="trainee" :href="trainee.url" class="text-ink-strong underline">{{ trainee.name }}</a>
                    <!-- `char_id` addresses the source's whole character space, not this catalog's, so the
                         join misses two ways: the 9000-block staff have no trainee row at all (ADR-0014
                         correction 1), and a card can name a trainee this catalog does not track. -->
                    <span
                        v-else
                        title="The character id the source gives this card has no row in the trainable catalog."
                    >
                        {{ card.char_name ?? 'This card' }} has no trainee page in this catalog.
                    </span>
                </dd>
            </div>
        </dl>

        <section class="mt-8" aria-labelledby="effects">
            <h2 id="effects" class="text-lg font-semibold text-ink-strong">Effects</h2>

            <p v-if="effects.length === 0" class="mt-2 text-sm text-ink-muted">
                The source states no effect anchor for this card.
            </p>
            <template v-else>
                <ul class="mt-2 flex flex-wrap gap-2 text-xs">
                    <li v-for="effect in effects" :key="effect.effect_id" class="rounded-md bg-sunken px-2 py-1 text-ink">
                        {{ effect.name ?? `[Unverified] effect ${effect.effect_id}` }} {{ effect.display }}
                    </li>
                </ul>

                <p class="mt-2 text-xs text-ink-muted">
                    Each figure is that effect's highest stated anchor, not an interpolation, and the anchor a
                    card stops at is not always level 50. An effect whose id the dictionary has no row for is
                    printed as its id rather than given a word this tool cannot source.
                </p>
            </template>
        </section>

        <section
            v-for="list in skillSections"
            :key="list.noun"
            class="mt-8"
            :aria-labelledby="`${list.noun}-skills`"
        >
            <h2 :id="`${list.noun}-skills`" class="text-lg font-semibold text-ink-strong">{{ list.heading }} skills</h2>

            <p v-if="!list.data.stored" class="mt-2 text-sm text-ink-muted">
                No {{ list.noun }} skill list is stored for this card.
            </p>
            <p v-else-if="list.data.skills.length === 0" class="mt-2 text-sm text-ink-muted">
                The source lists no {{ list.noun }} skills for this card.
            </p>
            <ul v-else class="mt-2">
                <SkillRow v-for="skill in list.data.skills" :key="skill.url" :skill="skill" />
            </ul>

            <p v-if="list.data.unlinked > 0" class="mt-2 text-xs text-ink-muted">
                {{ list.data.unlinked }} {{ list.noun }} skill ids in the source's list resolve to no skill
                page this catalog can open, so they are counted here rather than linked or dropped.
            </p>
        </section>

        <h2 class="mt-8 text-lg font-semibold text-ink-strong">Provenance</h2>
        <p v-if="card.source_url" class="mt-2 text-xs text-ink-muted">
            from
            <a :href="card.source_url" class="text-ink-strong underline">{{ card.source_url }}</a>
            <template v-if="card.fetched_at_display"> · read {{ card.fetched_at_display }}</template>
            <template v-if="card.is_manual">
                <!-- FR-B-4's stop sign, stated where a Trainer can see it: a row carrying it is theirs, and
                     the engine will not overwrite it on the next fetch. -->
                · <span class="text-ink-strong">Corrected by hand</span>, so the fetch engine leaves it alone
            </template>
        </p>
        <p v-else class="mt-2 text-sm text-ink-muted">No fetched source. This record was seeded or entered by hand.</p>
    </AppLayout>
</template>

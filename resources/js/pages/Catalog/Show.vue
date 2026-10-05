<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import AptitudeGrid from '../../components/AptitudeGrid.vue';
import SkillRow from '../../components/SkillRow.vue';
import FormDetail from '../../components/catalog/FormDetail.vue';
import FormTabs from '../../components/catalog/FormTabs.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Alias {
    alias: string;
    language_label: string;
}

interface Card {
    id: number;
    title: string;
    rarity_label: string;
    rarity_stars: string;
    is_debut_form: boolean;
    unconfirmed: boolean;
    global_release_date: string;
    global_release_date_display: string;
    snapshot_path: string | null;
    source_url: string;
    fetched_at_display: string | null;
}

interface Skill {
    id: number;
    name: string;
    sp_cost: number | null;
    is_unique: boolean;
    url: string | null;
}

interface SkillList {
    label: string;
    absent: string;
    has_ids: boolean;
    skills: Skill[];
}

interface Run {
    id: number;
    status_label: string;
    scenario_label: string;
    turn_count: number;
}

interface Source {
    url: string;
    source_key: string;
    fetched_at_display: string;
}

interface Profile {
    va_ja: string | null;
    va_en: string | null;
    birthday: { iso: string | null; display: string } | null;
    height: number | null;
    three_sizes: { b: number | null; h: number | null; w: number | null } | null;
}

interface Trainee {
    id: number;
    slug: string;
    name: string;
    name_ja: string | null;
    release_status_label: string;
    is_japan_only: boolean;
    japanese_name: string | null;
    jp_debut_date: string | null;
    global_debut_date: string | null;
    release_date: { iso: string | null; display: string; is_global: boolean } | null;
    is_manual: boolean;
    aptitudes: Record<string, string> | null;
    profile: Profile | null;
    aliases: Alias[];
    /** The Identity slot's loopback URL, or null when the mirror holds no file for the active form. */
    artworkURL: string | null;
}

const props = defineProps<{
    trainee: Trainee;
    cards: Card[];
    activeCardId: number | null;
    hiddenFormCount: number;
    runs: Run[];
    skillLists: SkillList[];
    provenance: Source[];
    showUnconfirmed: boolean;
}>();

const activeCard = (): Card | null => props.cards.find((card) => card.id === props.activeCardId) ?? null;

const turnLabel = (count: number): string => (count === 1 ? 'turn' : 'turns');
</script>

<template>
    <AppLayout>
        <Head :title="trainee.name" />
        <template #title>{{ trainee.name }}</template>

        <div class="flex justify-end">
            <Link href="/umamusume" class="text-sm text-ink-muted hover:underline">Back to catalog</Link>
        </div>

        <section class="mt-4" aria-labelledby="basic-information">
            <!-- The Identity slot (`ADR-0021` read half, `design-2.0` §45a "Catalog detail,
                 Identity"): the active form's portrait at the recorded `size-16`, as a leading cell
                 beside this section's heading rather than inside the shell banner, which is a thin
                 `py-3` chrome bar carrying the page name at `text-base` and is not where a 64px
                 frame belongs.

                 No `href`: §45a gives this slot the click action *no action*, and the component then
                 renders a bare frame with no anchor at all. That is the case the Blade component
                 could not express — it always emitted a link, which on a detail page meant a link to
                 the page already open, with an `aria-label` its own `decorative` flag had just
                 blanked. An unnamed focusable link fails WCAG 2.2 AA 4.1.2, so the honest reading of
                 §45a here is no anchor.

                 Decorative `alt=""`: the shell banner's `<h1>` is this trainee's name, so a screen
                 reader reads it once from the text (`design-2.0` §42). The frame renders only when
                 the mirror holds a file; otherwise the section keeps its heading and its text with no
                 box and no placeholder (`DESIGN.md` §4.7). -->
            <div class="flex items-center gap-3">
                <ArtworkSlot :url="trainee.artworkURL" alt="" size="size-16" />
                <h2 id="basic-information" class="text-lg font-semibold text-ink-strong">Basic information</h2>
            </div>

            <template v-if="!trainee.profile">
                <dl
                    v-if="trainee.japanese_name"
                    class="mt-2 grid grid-cols-2 gap-x-6 gap-y-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-3"
                >
                    <div class="col-span-2 md:col-span-3">
                        <dt class="text-ink-muted">Japanese name</dt>
                        <dd class="text-ink"><span lang="ja">{{ trainee.japanese_name }}</span></dd>
                    </div>
                </dl>
                <p class="mt-2 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink-muted">
                    No profile recorded for this trainee yet. It arrives with
                    `php artisan uma:fetch gametora-character-profiles`.
                </p>
            </template>

            <dl
                v-else
                class="mt-2 grid grid-cols-2 gap-x-6 gap-y-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-3"
            >
                <div class="col-span-2 md:col-span-3">
                    <dt class="text-ink-muted">Japanese name</dt>
                    <dd class="text-ink">
                        <span v-if="trainee.japanese_name" lang="ja">{{ trainee.japanese_name }}</span>
                        <template v-else>Not published by the source</template>
                    </dd>
                </div>

                <div>
                    <dt class="text-ink-muted">Voice actor</dt>
                    <dd class="text-ink">
                        {{ trainee.profile.va_ja ?? 'Not published by the source' }}
                        <span v-if="trainee.profile.va_en" class="text-ink-muted">(EN: {{ trainee.profile.va_en }})</span>
                        <span
                            v-else
                            class="block text-xs text-ink-muted"
                            title="The source lists no English voice actor for this trainee."
                        >No English dub listed</span>
                    </dd>
                </div>

                <div>
                    <dt class="text-ink-muted">Release date</dt>
                    <dd class="text-ink">
                        <template v-if="trainee.release_date?.is_global && trainee.release_date.iso">
                            <time :datetime="trainee.release_date.iso">{{ trainee.release_date.display }} (Global)</time>
                        </template>
                        <template v-else-if="trainee.release_date">
                            {{ trainee.release_date.display }} (JP only)
                        </template>
                    </dd>
                </div>

                <div>
                    <dt class="text-ink-muted">Birthday</dt>
                    <dd class="text-ink">
                        <template v-if="trainee.profile.birthday">
                            <time v-if="trainee.profile.birthday.iso" :datetime="trainee.profile.birthday.iso">
                                {{ trainee.profile.birthday.display }}
                            </time>
                            <span
                                v-else
                                title="The source publishes no birth year for this trainee."
                            >{{ trainee.profile.birthday.display }}</span>
                        </template>
                        <template v-else>Not published by the source</template>
                    </dd>
                </div>

                <div>
                    <dt class="text-ink-muted">Height</dt>
                    <dd class="text-ink">
                        <template v-if="trainee.profile.height !== null">{{ trainee.profile.height }} cm</template>
                        <template v-else>Not published by the source</template>
                    </dd>
                </div>

                <div>
                    <dt class="text-ink-muted">Three sizes</dt>
                    <dd class="text-ink">
                        <template v-if="trainee.profile.three_sizes">
                            {{ trainee.profile.three_sizes.b }} · {{ trainee.profile.three_sizes.h }} ·
                            {{ trainee.profile.three_sizes.w }} cm
                            <span class="block text-xs text-ink-muted">bust · height · waist</span>
                        </template>
                        <template v-else>Not published by the source</template>
                    </dd>
                </div>
            </dl>
        </section>

        <dl class="mt-6 grid grid-cols-2 gap-x-6 gap-y-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-4">
            <div>
                <dt class="text-ink-muted">Release status</dt>
                <dd class="text-ink">{{ trainee.release_status_label }}</dd>
            </div>
            <div>
                <dt class="text-ink-muted">JP debut</dt>
                <dd v-if="trainee.jp_debut_date" class="text-ink">{{ trainee.jp_debut_date }}</dd>
                <dd v-else class="text-ink">
                    <span title="The source publishes no JP debut date for this trainee.">N/A</span>
                </dd>
            </div>
            <div>
                <dt class="text-ink-muted">Global debut</dt>
                <dd v-if="trainee.global_debut_date" class="text-ink">{{ trainee.global_debut_date }}</dd>
                <dd v-else class="text-ink">
                    <span title="The source publishes no Global debut date for this trainee.">N/A</span>
                </dd>
            </div>
            <div>
                <dt class="text-ink-muted">Edited by Trainer</dt>
                <dd class="text-ink">{{ trainee.is_manual ? 'Yes (engine will not overwrite)' : 'No' }}</dd>
            </div>
        </dl>

        <p
            v-if="trainee.is_japan_only"
            class="mt-4 rounded-md border border-ink-faint bg-raised px-3 py-2 text-sm text-ink"
        >
            Not yet released on Global. JP-sourced data below.
        </p>

        <section class="mt-8" aria-labelledby="aptitudes">
            <h2 id="aptitudes" class="text-lg font-semibold text-ink-strong">Aptitude</h2>
            <AptitudeGrid v-if="trainee.aptitudes" class="mt-2" :aptitudes="trainee.aptitudes" />
            <p v-else class="mt-2 text-sm text-ink-muted">Aptitude not published for this trainee.</p>
        </section>

        <h2 class="mt-8 text-lg font-semibold text-ink-strong">Costume forms</h2>

        <p
            v-if="cards.length === 0"
            class="mt-2 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink-muted"
        >
            <template v-if="hiddenFormCount > 0">
                Every costume form recorded for this trainee is hidden as unconfirmed.
            </template>
            <template v-else>
                No Global costume cards recorded for this trainee yet. Forms arrive with
                `php artisan uma:fetch gametora-character-cards`.
            </template>
        </p>

        <template v-else>
            <p class="mt-2 text-xs text-ink-muted">
                Objectives and card images are not recorded for this form; `ADR-0012` records why.
            </p>

            <FormTabs
                v-if="cards.length > 1"
                :cards="cards"
                :active-card-id="activeCardId"
                :slug="trainee.slug"
                :show-unconfirmed="showUnconfirmed"
            >
                <FormDetail v-if="activeCard()" :card="activeCard()!" />
            </FormTabs>
            <div v-else class="mt-2">
                <FormDetail :card="cards[0]" />
            </div>
        </template>

        <p v-if="hiddenFormCount > 0" class="mt-2 text-xs text-ink-muted">
            {{ hiddenFormCount }} {{ hiddenFormCount === 1 ? 'form' : 'forms' }} hidden as unconfirmed ·
            <Link
                :href="`/umamusume/${trainee.slug}?show_unconfirmed=1`"
                class="text-ink-strong underline"
            >Show unconfirmed forms</Link>
        </p>

        <section class="mt-8" aria-labelledby="skills">
            <h2 id="skills" class="text-lg font-semibold text-ink-strong">Skills</h2>

            <p v-if="skillLists.every((list) => !list.has_ids)" class="mt-2 text-sm text-ink-muted">
                No skill lists are recorded for this form.
            </p>
            <template v-else>
                <template v-for="list in skillLists" :key="list.label">
                    <h3 class="mt-4 text-xs font-bold uppercase tracking-widest text-ink-muted">{{ list.label }}</h3>
                    <ul class="mt-1">
                        <SkillRow v-for="skill in list.skills" :key="skill.id" :skill="skill" />
                        <li v-if="list.skills.length === 0 && !list.has_ids" class="py-1.5 text-sm text-ink-muted">
                            {{ list.absent }}
                        </li>
                        <li v-if="list.skills.length === 0 && list.has_ids" class="py-1.5 text-sm text-ink-muted">
                            Recorded on this form but not in the skill catalog yet.
                        </li>
                    </ul>
                </template>
            </template>

            <p class="mt-2 text-xs text-ink-muted">
                From this form's stored <code>skills_unique</code>, <code>skills_innate</code>,
                <code>skills_awakening</code> and <code>skills_event</code> lists.
                <code>skills_evo</code> is recorded on the card and not listed here: most of its ids name
                a skill [Global] has not shipped, and the skill pages refuse those rows rather than link
                to a missing page.
            </p>
        </section>

        <section class="mt-8" aria-labelledby="goal-races">
            <h2 id="goal-races" class="text-lg font-semibold text-ink-strong">Goal races</h2>
            <p class="mt-2 text-sm text-ink-muted">
                Goal races are not recorded. The source publishes per-trainee goal races; this tool
                does not record them.
            </p>
        </section>

        <section class="mt-8" aria-labelledby="her-runs">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2">
                <h2 id="her-runs" class="text-lg font-semibold text-ink-strong">Her runs</h2>
                <a
                    href="/training-runs/create"
                    class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome"
                >
                    New run
                </a>
            </div>
            <p class="mt-1 text-xs text-ink-muted">Opens run setup; the trainee is chosen there.</p>

            <p v-if="runs.length === 0" class="mt-2 text-sm text-ink-muted">No runs recorded for her yet.</p>
            <ul v-else class="mt-2 divide-y divide-rule rounded-md border border-rule">
                <li
                    v-for="run in runs"
                    :key="run.id"
                    class="flex flex-wrap items-baseline gap-x-3 px-3 py-2 text-sm"
                >
                    <span class="font-semibold text-ink-strong">{{ run.status_label }}</span>
                    <span class="text-ink">{{ run.scenario_label }}</span>
                    <span class="ml-auto font-mono text-xs tabular-nums text-ink-muted">
                        {{ run.turn_count }} {{ turnLabel(run.turn_count) }}
                    </span>
                </li>
            </ul>
        </section>

        <h2 class="mt-8 text-lg font-semibold text-ink-strong">Aliases</h2>
        <p v-if="trainee.aliases.length === 0" class="mt-2 text-sm text-ink-muted">No aliases yet.</p>
        <ul v-else class="mt-2 flex flex-wrap gap-2 text-sm">
            <li
                v-for="(alias, index) in trainee.aliases"
                :key="index"
                class="rounded-md bg-sunken px-2 py-1 text-ink"
            >
                {{ alias.alias }} <span class="text-ink-muted">({{ alias.language_label }})</span>
            </li>
        </ul>

        <h2 class="mt-8 text-lg font-semibold text-ink-strong">Provenance</h2>
        <p v-if="provenance.length === 0" class="mt-2 text-sm text-ink-muted">
            No fetched sources. This record was seeded or entered by hand.
        </p>
        <template v-else>
            <ul class="mt-2 space-y-1 text-sm">
                <li v-for="source in provenance" :key="source.url" class="text-ink">
                    <a :href="source.url" class="text-ink-strong underline">{{ source.url }}</a>
                    <span class="text-ink-muted"> · {{ source.source_key }} · fetched {{ source.fetched_at_display }}</span>
                </li>
            </ul>
            <p class="mt-2 text-xs text-ink-muted">
                The rows above are this trainee's own fetch history. The profile block and each
                costume form name the source and the date their own row was read from.
            </p>
        </template>
    </AppLayout>
</template>

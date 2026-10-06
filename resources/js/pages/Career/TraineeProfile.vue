<script setup lang="ts">
/*
 * SCREEN-004, Trainee Profile (`ADR-0020` §1; `docs/proposals/screen-spec-2.0.md` §7). The read screen
 * behind SCR-CAR-003's "View Profile" action: everything the catalog holds about one trainee, presented
 * before the choice is made.
 *
 * **Two grains, and the page keeps them apart.** Rarity is a `character_cards` column, so it is printed
 * per costume form; there is no trainee-level rarity column and no aggregate badge is offered. Aptitude is
 * a set of `umamusume` columns, so the ten letters appear exactly once (ADR-0008). `AptitudeGrid.vue` is
 * that one appearance, and `AptitudeBadge.vue` is the roster's compact read of the same data.
 *
 * **Sections the brief names that this repository does not have.** `TraineeProfileController`'s docblock
 * carries every citation; what the page does about each:
 *
 * - **Career goals is not rendered at all.** `trainee_goals` exists in no migration, model or factory, and
 *   `race_catalog_slots`' own comment says its obligation rows are scenario-scoped and "deliberately not
 *   the per-character Goal". The filing is reserved as KI-34. An empty heading would advertise a fact the
 *   tool is known not to hold, so there is no heading.
 * - **Hint skills and evolution skills are not rendered.** `character_cards` has no `skills_hint` column
 *   (hints live only on `support_cards`), and `skills_evo` is stored but SCR-CAT-002 refuses to list it.
 * - **Growth rates are not rendered**, for the same two citations the roster screen names.
 * - **Version, recommended stat distribution, useful inheritance and useful support types are named
 *   absences**, each `N/A` with the reason on its `title`, because the brief asks for them and the schema
 *   cannot answer.
 * - **Ideal distances and suitable running styles are labelled aptitude.** The letters are the only
 *   trainee-side signal stored; "ideal" would be a judgement no column holds.
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import RarityChip from '../../components/RarityChip.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import AptitudeGrid from '../../components/AptitudeGrid.vue';
import SkillRow from '../../components/SkillRow.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface SkillEntry {
    name: string;
    sp_cost: number | null;
    is_unique: boolean;
    url: string;
}

interface SkillList {
    key: string;
    label: string;
    skills: SkillEntry[];
    unresolved: number;
}

interface FormEntry {
    id: number;
    title: string;
    rarity_label: string;
    rarity_stars: string;
    rarity_word: string;
    is_debut_form: boolean;
    global_release_date: string;
    global_release_date_display: string;
    artworkURL: string | null;
    skillLists: SkillList[];
}

interface ProfileBlock {
    va_ja: string | null;
    va_en: string | null;
    height: number | null;
    birthday: { iso: string | null; display: string } | null;
    three_sizes: { b: number; h: number; w: number } | null;
}

interface Trainee {
    id: number;
    slug: string;
    name: string;
    name_ja: string | null;
    release_status_label: string;
    is_manual: boolean;
    aptitudes: Record<string, string> | null;
    profile: ProfileBlock | null;
    artworkURL: string | null;
}

interface Absence {
    value: string | null;
    title: string;
}

const props = defineProps<{
    trainee: Trainee;
    forms: FormEntry[];
    selected: number | null;
    scenarioLabel: string | null;
    scenarioPending: boolean;
    absences: {
        version: Absence;
        stat_distribution: Absence;
        inheritance: Absence;
        support_types: Absence;
    };
}>();

const isSelected = computed(() => props.selected === props.trainee.id);

const form = useForm<{ umamusume_id: number }>({ umamusume_id: props.trainee.id });
const saving = ref(false);

function choose(): void {
    saving.value = true;

    // Literal URL, as everywhere in this app: no Ziggy, so `router.route()` would throw on click. The PUT
    // redirects to the roster step, where the stored choice is read back from the session draft, so this
    // screen never claims a selection from client memory alone.
    form.put('/career/setup/trainee', {
        preserveScroll: true,
        onFinish: () => {
            saving.value = false;
        },
    });
}

const profile = computed(() => props.trainee.profile);
</script>

<template>
    <SetupLayout :step="2">
        <Head :title="props.trainee.name" />
        <template #title>{{ props.trainee.name }}</template>

        <p class="flex flex-wrap items-center gap-3 text-sm">
            <Link href="/career/setup/trainee" class="inline-flex min-h-11 items-center rounded-md px-2 text-ink underline">
                Back to the roster
            </Link>
            <span class="text-ink-muted">
                Scenario:
                <strong v-if="props.scenarioLabel" class="text-ink-strong">{{ props.scenarioLabel }}</strong>
                <span v-else class="text-ink-muted" title="No scenario has been chosen in this setup draft yet.">
                    N/A
                </span>
            </span>
        </p>

        <section class="mt-6" aria-labelledby="basic-information">
            <h2 id="basic-information" class="text-lg font-semibold text-ink-strong">Basic</h2>

            <div class="mt-3 flex flex-wrap items-start gap-4">
                <!-- The header prints her name beside the frame, so the image is decorative and takes
                     `alt=""` (DESIGN.md §4.7). -->
                <ArtworkSlot :url="props.trainee.artworkURL" alt="" size="size-16" />
                <p class="text-lg font-semibold text-ink-strong">
                    {{ props.trainee.name }}
                    <span v-if="props.trainee.name_ja" lang="ja" class="ml-2 text-base font-normal text-ink-muted">
                        {{ props.trainee.name_ja }}
                    </span>
                </p>
            </div>

            <dl class="mt-4 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                <div class="flex flex-wrap justify-between gap-x-4">
                    <dt class="text-ink-muted">Availability</dt>
                    <dd class="text-ink">{{ props.trainee.release_status_label }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-x-4">
                    <dt class="text-ink-muted">Voice actor</dt>
                    <dd v-if="profile?.va_ja" class="text-ink">
                        <span lang="ja">{{ profile.va_ja }}</span>
                        <span v-if="profile.va_en" class="text-ink-muted"> · {{ profile.va_en }}</span>
                    </dd>
                    <dd v-else class="text-ink-muted" title="No profile document has been fetched for this trainee.">N/A</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-x-4">
                    <dt class="text-ink-muted">Birthday</dt>
                    <dd v-if="profile?.birthday" class="text-ink">
                        <time v-if="profile.birthday.iso" :datetime="profile.birthday.iso">{{ profile.birthday.display }}</time>
                        <span v-else :title="'The source published a month and day but no year.'">{{ profile.birthday.display }}</span>
                    </dd>
                    <dd v-else class="text-ink-muted" title="The profile document states no birthday for this trainee.">N/A</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-x-4">
                    <dt class="text-ink-muted">Height</dt>
                    <dd v-if="profile?.height !== null && profile?.height !== undefined" class="text-ink">{{ profile.height }} cm</dd>
                    <dd v-else class="text-ink-muted" title="The profile document states no height for this trainee.">N/A</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-x-4">
                    <dt class="text-ink-muted">Version</dt>
                    <!-- A named absence, not a blank: the brief asks for a version and no column holds one
                         (`app.ruleset` is null by ruling, and there is no trainee version either). -->
                    <dd class="text-ink-muted" :title="props.absences.version.title">
                        {{ props.absences.version.value ?? 'N/A' }}
                    </dd>
                </div>
                <div class="flex flex-wrap justify-between gap-x-4">
                    <dt class="text-ink-muted">Edited by you</dt>
                    <dd class="text-ink">{{ props.trainee.is_manual ? 'Yes' : 'No' }}</dd>
                </div>
            </dl>
        </section>

        <section class="mt-8" aria-labelledby="aptitudes">
            <h2 id="aptitudes" class="text-lg font-semibold text-ink-strong">Aptitude</h2>

            <p class="mt-1 max-w-2xl text-sm text-ink-muted">
                The trainee's own ten letters, once for the whole roster entry rather than once per costume
                form. These are aptitudes as the source states them, not a judgement about which distance
                or style is ideal for a career.
            </p>

            <div class="mt-4 max-w-2xl">
                <AptitudeGrid v-if="props.trainee.aptitudes" :aptitudes="props.trainee.aptitudes" />
                <p v-else class="text-sm text-ink-muted" title="The character document published no aptitude letters for this trainee.">
                    Aptitude: N/A
                </p>
            </div>
        </section>

        <section class="mt-8" aria-labelledby="costume-forms">
            <h2 id="costume-forms" class="text-lg font-semibold text-ink-strong">Costume forms</h2>

            <p class="mt-1 max-w-2xl text-sm text-ink-muted">
                Rarity is a fact about a costume form, so it is printed here and nowhere else; this catalog
                has no trainee-level rarity column to read.
            </p>

            <p
                v-if="props.forms.length === 0"
                class="mt-4 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink-muted"
                title="Every form for this trainee is confirmed by only one source, so none is shown."
            >
                No confirmed costume form. Her unique and starting skills come from a form, so nothing below
                this line can be stated until one is confirmed by two sources.
            </p>

            <!-- Forms are stacked rather than tabbed. `FormTabs.vue` is the catalog detail's control and it
                 builds its own hrefs as `/umamusume/{slug}?form=`, which would move a Trainer out of the
                 setup wizard to change a form on a wizard screen. -->
            <ul v-else class="mt-4 space-y-6">
                <li v-for="card in props.forms" :key="card.id" class="rounded-md border border-rule bg-panel p-4">
                    <h3 class="flex flex-wrap items-center gap-3">
                        <ArtworkSlot :url="card.artworkURL" alt="" size="size-10" reserve />
                        <span class="text-base font-semibold text-ink-strong">{{ card.title }}</span>
                        <span class="flex flex-wrap items-baseline gap-3 text-xs text-ink-muted">
                            <RarityChip :label="card.rarity_label" :stars="card.rarity_stars" />
                            <span class="font-mono">{{ card.rarity_word }}</span>
                            <span v-if="card.is_debut_form">debut form</span>
                            <time :datetime="card.global_release_date">Released (Global) {{ card.global_release_date_display }}</time>
                        </span>
                    </h3>

                    <div class="mt-4 grid gap-6 lg:grid-cols-2">
                        <div v-for="list in card.skillLists" :key="list.key">
                            <h4 class="text-sm font-semibold text-ink-strong">{{ list.label }}</h4>

                            <p
                                v-if="list.skills.length === 0 && list.unresolved === 0"
                                class="mt-1 text-sm text-ink-muted"
                                :title="`The source lists no ${list.label.toLowerCase()} for this form.`"
                            >
                                N/A
                            </p>

                            <ul v-else class="mt-1">
                                <li v-for="(skill, index) in list.skills" :key="index">
                                    <SkillRow :skill="skill" />
                                </li>
                            </ul>

                            <!-- Disclosed rather than dropped: the id is on the source's form and names no
                                 skill this tool can show on Global, so the count is stated and the name is
                                 not invented. -->
                            <p v-if="list.unresolved > 0" class="mt-1 text-xs text-ink-muted">
                                {{ list.unresolved }}
                                {{ list.unresolved === 1 ? 'id' : 'ids' }}
                                on this form name no skill released on Global.
                            </p>
                        </div>
                    </div>
                </li>
            </ul>
        </section>

        <section class="mt-8" aria-labelledby="build-analysis">
            <h2 id="build-analysis" class="text-lg font-semibold text-ink-strong">Build analysis</h2>

            <p class="mt-1 max-w-2xl text-sm text-ink-muted">
                The brief's four remaining figures are not stored anywhere in this catalog. They are printed
                as absences with their reasons rather than filled by a guess, and no career-goal section
                appears at all: `trainee_goals` has never been migrated, and the per-character Goal filing
                is reserved as KI-34.
            </p>

            <dl class="mt-4 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                <div class="flex flex-wrap justify-between gap-x-4">
                    <dt class="text-ink-muted">Recommended stat distribution</dt>
                    <dd class="text-ink-muted" :title="props.absences.stat_distribution.title">
                        {{ props.absences.stat_distribution.value ?? 'N/A' }}
                    </dd>
                </div>
                <div class="flex flex-wrap justify-between gap-x-4">
                    <dt class="text-ink-muted">Useful inheritance</dt>
                    <dd class="text-ink-muted" :title="props.absences.inheritance.title">
                        {{ props.absences.inheritance.value ?? 'N/A' }}
                    </dd>
                </div>
                <div class="flex flex-wrap justify-between gap-x-4">
                    <dt class="text-ink-muted">Useful support types</dt>
                    <dd class="text-ink-muted" :title="props.absences.support_types.title">
                        {{ props.absences.support_types.value ?? 'N/A' }}
                    </dd>
                </div>
                <div class="flex flex-wrap justify-between gap-x-4">
                    <dt class="text-ink-muted">Distance and running style</dt>
                    <!-- The one figure the brief asks for that the schema can answer, answered as what it
                         actually is: the letters above, read as aptitude. -->
                    <dd class="text-ink">Stated as aptitude above</dd>
                </div>
            </dl>
        </section>

        <p class="mt-8 flex flex-wrap items-center gap-3">
            <button
                id="trainee-select"
                type="button"
                class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome disabled:cursor-wait disabled:bg-disabled"
                :aria-pressed="isSelected ? 'true' : 'false'"
                :disabled="form.processing"
                @click="choose"
            >
                {{ saving ? 'Saving…' : 'Select this trainee' }}
            </button>
            <!-- The stored word sits beside the action, never inside a heading, so the page's own name stays
                 its accessible name. -->
            <span v-if="isSelected" class="text-xs font-bold text-ink-muted">Chosen</span>
        </p>
    </SetupLayout>
</template>

<script setup lang="ts">
/*
 * SCREEN-004, Trainee Profile (`ADR-0020` §1; `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md` §7).
 * The read screen behind SCR-CAR-003's "View Profile" action: everything the catalog holds about one
 * trainee, presented before the choice is made.
 *
 * Phase 4 (wireframe parity) rebuilt this page to `career-profile.html`'s two-column read screen:
 * aptitudes as a three-column table, a five-cell growth row, the unique skill, five base-stat rows, the
 * costume-form table with its current row marked, and the scenario-link pills. The two doors live in the
 * head band's `.hrow-actions`, where the wireframe draws them; the footer pair this page used to carry
 * is gone.
 *
 * HFW-OVERRIDE (excess: retained Basic card) — `career-profile.html` draws no profile-facts card, but
 * the facts here (voice actor, birthday, height, three sizes, availability, version absence) come from
 * `umamusume_profiles`, are stated with their absences, and are the page's only provenance block. The
 * card is retained at the foot of the right column rather than deleted; the wireframe's own "absence
 * keeps its row" rule is the reason.
 *
 * HFW-OVERRIDE (excess: header portrait) — the wireframe draws a 64px `card_portrait` slot beside the
 * name. The head band is the shell's (`SetupLayout`), whose `#title` slot is a heading; a frame inside an
 * `h1` would put artwork in the heading's accessible name. The form rows below carry the portraits
 * instead, at the 40px size `DESIGN.md` §4.7 records.
 *
 * HFW-OVERRIDE (copy: heading "Costume forms" vs the wireframe's "Forms") — "Costume forms" is the
 * grain the card data is stored at (`character_cards`), and the heading is retained so the page keeps
 * naming the row's unit rather than restating the wireframe's shorter mock label.
 *
 * HFW-OVERRIDE (computed/rect: the wizard-wide class) — the per-page reports
 * (`.scratch-uma/wizard-parity/profile-diff-{1280,390}.json`, written by `career-profile.spec.ts`)
 * measure the app's DESIGN.md tokens against the wireframe's mock CSS and the shell's wrapper offset.
 * The two are two implementations of one design, not one tree rendered twice: tokens outrank the mock
 * palette (`AGENTS.md` §2), and the wrapper moves every box, so the computed and rect buckets are
 * measured evidence rather than empty lists. Text is the bucket asserted empty, and it is.
 *
 * **Two grains, and the page keeps them apart.** Rarity is a `character_cards` column, so it is printed
 * per costume form; there is no trainee-level rarity column and no aggregate badge is offered. Aptitude
 * is a set of `umamusume` columns, so the ten letters appear exactly once (ADR-0008).
 *
 * **Sections the brief names that this repository does not have.** `TraineeProfileController`'s docblock
 * carries every citation. In short: career goals are omitted (the ladder has two homes, her catalog
 * detail page and the Cockpit's Trainee goals group, and a third copy would drift); hint and evolution
 * skills are not on a trainee; version is a named absence; "ideal" distances and styles are printed as
 * aptitude because no column holds a judgement.
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import RarityChip from '../../components/RarityChip.vue';
import SkillRow from '../../components/SkillRow.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import { BaseButton, LinkButton } from '../../components/Form';
import { Head, useForm } from '@inertiajs/vue3';
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
    /** The growth percentages the card is read into; a null axis is an absence, not a zero. */
    growth: Record<string, number | null>;
    /** `config('scenarios')`'s labels, in display order: what this trainee may be linked to. */
    scenarioLinks: string[];
    absences: {
        version: Absence;
    };
    /** `route('career.trainee.store')`, supplied by the server so the page never hard-codes the URL. */
    action: string;
}>();

const isSelected = computed(() => props.selected === props.trainee.id);

const form = useForm<{ umamusume_id: number }>({ umamusume_id: props.trainee.id });
const saving = ref(false);

function choose(): void {
    saving.value = true;

    // The write URL is supplied by the server (`career.trainee.store`) so the page never states it as
    // a literal. The PUT redirects to the target step, where the stored choice is read back from the
    // session draft, so this screen never claims a selection from client memory alone.
    form.put(props.action, {
        preserveScroll: true,
        onFinish: () => {
            saving.value = false;
        },
    });
}

const profile = computed(() => props.trainee.profile);

/*
 * The five stat axes, in the order every other screen prints them. The growth row and the base-stat
 * block are both keyed on this list so the two never disagree about which axes exist.
 *
 * Neither value comes from the catalogue: `umamusume` carries no growth column and no base-stat column
 * (`TraineeSelectController` omits the growth filter for exactly that reason). Growth is a real zero -
 * this tool records no growth bonus for a trainee, so each axis contributes 0% - while base stats are a
 * genuine absence, printed as a dash with the reason in text. The wireframe prints zeros as `+0%` and
 * always five cells (`UMAMUSUME_REFERENCE.md` §1.3.5), so a zero keeps its tile and never renders blank.
 */
const statAxes = ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'] as const;

function growthValue(axis: string): number | null {
    return props.growth[axis] ?? null;
}

function growthLabel(axis: string): string {
    const value = growthValue(axis);

    return value === null ? 'Not recorded' : `+${value}%`;
}

/*
 * The aptitudes as `career-profile.html` draws them: three columns (surface, distance, running style),
 * the strongest letter of each family first and bold, the rest muted beside it. The ten letters are the
 * trainee's own (ADR-0008), so they appear on this page once and never per costume form.
 *
 * HFW-OVERRIDE (sourced: style names) — the wireframe abbreviates "Front Runner" to "Front" and
 * "Pace Chaser" to "Pace". The Global client's full words are the sourced vocabulary
 * (`lang/en/uma.php`), so the table prints them rather than the mock's abbreviations.
 */
const APTITUDE_GROUPS: { header: string; cells: [label: string, key: string][] }[] = [
    {
        header: 'Surface',
        cells: [
            ['Turf', 'turf'],
            ['Dirt', 'dirt'],
        ],
    },
    {
        header: 'Distance',
        cells: [
            ['Sprint', 'sprint'],
            ['Mile', 'mile'],
            ['Medium', 'medium'],
            ['Long', 'long'],
        ],
    },
    {
        header: 'Running style',
        cells: [
            ['Front Runner', 'front_runner'],
            ['Pace Chaser', 'pace_chaser'],
            ['Late Surger', 'late_surger'],
            ['End Closer', 'end_closer'],
        ],
    },
];

const GRADE_ORDER = 'SABCDEFG';

function gradeRank(letter: string): number {
    const rank = GRADE_ORDER.indexOf(letter.toUpperCase());

    return rank === -1 ? GRADE_ORDER.length : rank;
}

interface AptitudeGroup {
    header: string;
    entries: { label: string; key: string; letter: string }[];
}

const aptitudeGroups = computed<AptitudeGroup[] | null>(() => {
    const letters = props.trainee.aptitudes;

    if (letters === null) {
        return null;
    }

    return APTITUDE_GROUPS.map((group) => {
        const entries = group.cells.map(([label, key]) => ({ label, key, letter: letters[key] ?? '—' }));

        entries.sort((a, b) => gradeRank(a.letter) - gradeRank(b.letter));

        return { header: group.header, entries };
    });
});
</script>

<template>
    <SetupLayout :step="2">
        <Head :title="props.trainee.name" />
        <template #eyebrow>Trainee profile</template>
        <template #title>
            {{ props.trainee.name }}
            <span v-if="props.trainee.name_ja" lang="ja" class="ml-2 text-base font-normal text-ink-muted">{{ props.trainee.name_ja }}</span>
        </template>

        <!--
          The wireframe's head-band pair (`career-profile.html:64-68`): the way back to the roster and the
          one write on the page. The primary still submits the form so the choice is recorded server-side
          before the target step reads it back from the session draft; `Chosen` sits beside the pair
          rather than inside either control, and the `aria-pressed` state is what the roster round-trip
          case reads after selecting from the card list.
        -->
        <template #actions>
            <LinkButton href="/career/setup/trainee" variant="secondary">Choose a different trainee</LinkButton>
            <BaseButton
                id="trainee-select"
                type="button"
                variant="primary"
                :aria-pressed="isSelected ? 'true' : 'false'"
                :disabled="form.processing"
                @click="choose"
            >
                {{ saving ? 'Saving…' : 'Continue to build target' }}
            </BaseButton>
            <span v-if="isSelected" class="text-xs font-bold text-ink-muted">Chosen</span>
        </template>

        <!-- This is a read screen, and it says so before the first fact, in the wireframe's own words. -->
        <p class="max-w-prose text-sm text-ink-muted">
            This is a read screen: nothing here is edited in the wizard. It states what the catalog holds
            about her before the choice is made, and the two controls in the head band are the only ones on it.
        </p>

        <div class="mt-4 grid gap-4 g-side">
            <!-- LEFT: aptitudes, the growth row, her one unique skill. -->
            <div class="space-y-4">
                <section class="card" aria-labelledby="aptitudes">
                    <h2 id="aptitudes">Aptitudes</h2>

                    <p class="note mt-2">
                        These are the aptitudes the source states, not a judgement about which distance or
                        style suits a career. One letter per axis; the strongest of each family is bold.
                    </p>

                    <table v-if="aptitudeGroups" class="mt-3 w-full text-sm">
                        <thead>
                            <tr class="border-b border-rule text-left text-xs font-semibold uppercase tracking-wider text-ink-muted">
                                <th v-for="group in aptitudeGroups" :key="group.header" class="py-2 pr-3">{{ group.header }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="align-top">
                                <td v-for="group in aptitudeGroups" :key="group.header" class="py-2 pr-3">
                                    <dl class="space-y-0.5">
                                        <div v-for="(entry, index) in group.entries" :key="entry.key" class="flex items-baseline gap-1.5">
                                            <dt class="text-ink-muted">{{ entry.label }}</dt>
                                            <dd :class="index === 0 ? 'font-mono text-ink-strong' : 'font-mono text-ink-muted'">
                                                <b v-if="index === 0">{{ entry.letter }}</b>
                                                <template v-else>{{ entry.letter }}</template>
                                            </dd>
                                        </div>
                                    </dl>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="mt-3 text-sm text-ink-muted">
                        <AbsenceValue compact reason="The character document published no aptitude letters for this trainee." />
                    </p>

                    <!-- The page's defence against conflating two scales, in the wireframe's position. -->
                    <p class="note mt-2">
                        An aptitude letter (S to G) grades how well she handles a surface, distance or running
                        style. A stat grade grades a number out of the run's cap. They are different scales and
                        neither converts into the other, so a strong aptitude letter says nothing about how high
                        a stat will climb.
                    </p>
                </section>

                <section class="card" aria-labelledby="growth">
                    <h2 id="growth">Growth row</h2>

                    <div class="tiles mt-2" data-field="growth">
                        <div v-for="axis in statAxes" :key="axis" class="tile">
                            <p class="k">{{ axis }}</p>
                            <p class="v" :class="{ na: growthValue(axis) === null }">{{ growthLabel(axis) }}</p>
                        </div>
                    </div>

                    <p class="note mt-2">
                        Every axis reads +0% because this tool records no growth bonus for a trainee: the
                        catalogue holds her aptitude letters and her costume forms, and a run starts from the
                        trainee's own baseline. A percentage here that is not zero would be a figure no source
                        in this repository states.
                    </p>
                </section>

                <section class="card" aria-labelledby="unique-skill">
                    <h2 id="unique-skill">Unique skill</h2>

                    <template v-for="card in props.forms" :key="`unique-${card.id}`">
                        <ul v-for="list in card.skillLists.filter((entry) => entry.key === 'skills_unique')" :key="list.key" class="mt-2">
                            <li v-for="(skill, index) in list.skills" :key="index"><SkillRow :skill="skill" /></li>
                        </ul>
                    </template>

                    <p v-if="!props.forms.some((card) => card.skillLists.some((list) => list.key === 'skills_unique' && list.skills.length > 0))" class="mt-2 text-sm text-ink-muted">
                        <AbsenceValue compact reason="No confirmed costume form for this trainee names a unique skill, so none is stated here." />
                    </p>

                    <!-- The skill row prints the skill's own SP cost. This note says plainly that the setup
                         screens never apply a discount to it, so a figure read here is never mistaken for a
                         price this run would pay (the SP ladder is a cockpit mechanic, not a wizard one). -->
                    <p class="note mt-2">
                        The SP figure on a skill is its own published cost. No setup screen applies a discount
                        to it.
                    </p>
                </section>
            </div>

            <!-- RIGHT: base stats (a stated absence), the forms, the scenario links, the provenance block. -->
            <div class="space-y-4">
                <section class="card" aria-labelledby="base-stats">
                    <h2 id="base-stats">Base stats</h2>

                    <p class="note mt-2">What the card starts with, before any training.</p>

                    <div class="mt-2" data-field="base-stats">
                        <div v-for="axis in statAxes" :key="axis" class="srow">
                            <span class="k">{{ axis }}</span>
                            <span class="v">—</span>
                        </div>
                    </div>

                    <!-- The HFW prints the dash with this note; the rows stay rather than being deleted,
                         because a missing row and a missing figure are different claims. -->
                    <p class="note mt-2">
                        Base stats are not carried by the catalogue. None of these figures is known before a
                        run exists, so each row prints a dash instead of a number. The 450-point cap the
                        cockpit works from is a run-scoped ceiling, not a starting value.
                    </p>
                </section>

                <section class="card" aria-labelledby="forms">
                    <h2 id="forms">Costume forms</h2>

                    <p class="note mt-2">
                        Rarity belongs to a costume form, so it is printed per form and never as a
                        trainee-level badge.
                    </p>

                    <p v-if="props.forms.length === 0" class="mt-2 rounded-md border border-dashed border-rule bg-raised p-3 text-sm text-ink-muted">
                        No confirmed costume form. Her unique and starting skills come from a form, so nothing
                        here can be stated until one is confirmed by two sources.
                    </p>

                    <table v-else class="mt-2 w-full text-sm">
                        <thead>
                            <tr class="border-b border-rule text-left text-xs font-semibold uppercase tracking-wider text-ink-muted">
                                <th class="py-2 pr-3">Title</th>
                                <th class="pr-3">Rarity</th>
                                <th class="text-right">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="card in props.forms"
                                :key="card.id"
                                class="border-b border-rule align-top"
                                :class="card.is_debut_form ? 'is-current' : ''"
                            >
                                <td class="py-2 pr-3">
                                    <span class="flex items-center gap-2">
                                        <ArtworkSlot :url="card.artworkURL" :alt="card.title" size="size-10" />
                                        <span>
                                            <b class="text-ink-strong">{{ card.title }}</b>
                                            <span class="block text-xs text-ink-muted">
                                                <template v-if="card.is_debut_form">Debut form · </template>
                                                <time :datetime="card.global_release_date">{{ card.global_release_date_display }}</time>
                                            </span>
                                        </span>
                                    </span>
                                </td>
                                <td class="pr-3"><RarityChip :label="card.rarity_label" :stars="card.rarity_stars" /></td>
                                <td class="text-right">
                                    <span v-if="card.is_debut_form" class="pill pill-positive">Selected</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section class="card" aria-labelledby="scenarios">
                    <h2 id="scenarios">Scenarios this trainee is linked to</h2>

                    <p class="note mt-2">
                        A scenario link decides which scenario cards name this trainee. It does not lock the choice.
                    </p>

                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span v-for="label in props.scenarioLinks" :key="label" class="pill pill-outline">{{ label }}</span>
                    </div>

                    <p class="note mt-2">
                        No per-trainee link list is carried on this payload, so the scenarios are shown as
                        available rather than as links. The scenario step is where the run's choice is made.
                    </p>
                </section>

                <!-- The retained provenance block; the override note at the top of this file names why the
                     wireframe's omission is not followed. -->
                <section class="card" aria-labelledby="basic-information">
                    <h2 id="basic-information">Basic</h2>

                    <dl class="mt-2 space-y-1 text-sm">
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
                            <dd v-else><AbsenceValue compact reason="No profile document has been fetched for this trainee." /></dd>
                        </div>
                        <div class="flex flex-wrap justify-between gap-x-4">
                            <dt class="text-ink-muted">Birthday</dt>
                            <dd v-if="profile?.birthday" class="text-ink">
                                <time v-if="profile.birthday.iso" :datetime="profile.birthday.iso">{{ profile.birthday.display }}</time>
                                <span v-else>{{ profile.birthday.display }}</span>
                            </dd>
                            <dd v-else><AbsenceValue compact reason="The profile document states no birthday for this trainee." /></dd>
                        </div>
                        <div class="flex flex-wrap justify-between gap-x-4">
                            <dt class="text-ink-muted">Height</dt>
                            <dd v-if="profile?.height !== null && profile?.height !== undefined" class="text-ink">{{ profile.height }} cm</dd>
                            <dd v-else><AbsenceValue compact reason="The profile document states no height for this trainee." /></dd>
                        </div>
                        <div class="flex flex-wrap justify-between gap-x-4">
                            <dt class="text-ink-muted">Three sizes</dt>
                            <dd v-if="profile?.three_sizes" class="text-ink">
                                {{ profile.three_sizes.b }} · {{ profile.three_sizes.h }} · {{ profile.three_sizes.w }} cm
                                <span class="block text-xs text-ink-muted">bust · height · waist</span>
                            </dd>
                            <dd v-else><AbsenceValue compact reason="The profile document states no three-sizes measurement for this trainee." /></dd>
                        </div>
                        <div class="flex flex-wrap justify-between gap-x-4">
                            <dt class="text-ink-muted">Version</dt>
                            <dd><AbsenceValue compact :reason="props.absences.version.title" /></dd>
                        </div>
                        <div class="flex flex-wrap justify-between gap-x-4">
                            <dt class="text-ink-muted">Edited by you</dt>
                            <dd class="text-ink">{{ props.trainee.is_manual ? 'Yes' : 'No' }}</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>

        <p class="mt-6 max-w-3xl text-xs text-ink-muted">
            This page carries no career-goal section by design: the trainee's own objectives are fetched per
            character and read under Trainee goals in the Cockpit's Scenario panel, where the run's race
            record decides whether each one is met.
        </p>
    </SetupLayout>
</template>

<script setup lang="ts">
/*
 * SCREEN-004, Trainee Profile (`ADR-0020` §1; `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md` §7).
 * The read screen between the trainee roster and the build target: everything the catalog holds about
 * one trainee, presented before the choice is committed.
 *
 * Phase 4 rebuilt this page to `career-profile.html`'s two-column read screen; this slice put it on the
 * wizard's path, so the roster's forward door opens it (`career-trainee.html:64`) and its own primary
 * continues to the build target (`career-profile.html:67`). The two doors live in the head band's
 * `.hrow-actions`, where the wireframe draws them.
 *
 * HFW-OVERRIDE (data: growth and base stats are N/A, not +0% or a dash) — the wireframe prints five
 * `+0%` growth cells and five `—` base rows because those figures exist on the game's card. This
 * catalogue stores neither: the Growth Rate row (`stat_bonus`) and the base five are absent from every
 * table it owns. The rows keep their cells (an absent figure keeps its row) and print `N/A` under one
 * visible reason per block, which is what the quality bar requires of an unrecorded value.
 *
 * HFW-OVERRIDE (copy: lede identity line) — `career-profile.html:61` opens the lede with the form, its
 * rarity and the strongest aptitudes in one sentence. Rarity is a costume form's fact, not a trainee's
 * (ADR-0008), so a trainee-level line would present one form's rarity as hers; the form table states the
 * per-form grain and the aptitude table states the letters. The read-screen sentence stays.
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
 * measured evidence rather than empty lists. The text bucket reports differences rather than an empty
 * list: the mock's identity lede, placeholder skill and illustrative figures are not this page's copy,
 * and the content the page must draw is asserted case by case in its spec.
 *
 * **Two grains, and the page keeps them apart.** Rarity is a `character_cards` column, so it is printed
 * per costume form; there is no trainee-level rarity column and no aggregate badge is offered. Aptitude
 * is a set of `umamusume` columns, so the ten letters appear exactly once (ADR-0008).
 *
 * **Sections the brief names that this repository does not have.** `TraineeProfileController`'s docblock
 * carries every citation. In short: career goals are omitted (the ladder has two homes, her catalog
 * detail page and the Cockpit's Trainee goals group, and a third copy would drift); hint and evolution
 * skills are not on a trainee; version left with the Basic facts card, so this page no longer states it
 * (the catalog detail still does); "ideal" distances and styles are printed as aptitude because no
 * column holds a judgement.
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

interface Trainee {
    id: number;
    name: string;
    name_ja: string | null;
    aptitudes: Record<string, string> | null;
    artworkURL: string | null;
}

const props = defineProps<{
    /** The steps this session has opened, the step bar's marking reads them. */
    visited: number[];
    /** The steps that have data stored in the draft (`SetupDraft::saved()`), read apart from `visited`. */
    saved: number[];
    choices: { label: string; value: string | null; url: string | null }[];
    trainee: Trainee;
    forms: FormEntry[];
    selected: number | null;
    /** The costume form the draft already names for this trainee, or null before a pick. */
    selectedCardId: number | null;
    /** `config('scenarios')`'s labels, in display order: what this trainee may be linked to. */
    scenarioLinks: string[];
    /** One named absence per block, each with the reason the row keeps its cells. */
    absences: {
        growth: { value: null; absence: string };
        baseStats: { value: null; absence: string };
    };
    /** `route('career.trainee')`, supplied by the server so the back door is never a literal. */
    rosterUrl: string;
    /** `route('career.trainee.store')`, supplied by the server so the page never hard-codes the URL. */
    action: string;
}>();

const isSelected = computed(() => props.selected === props.trainee.id);

/*
 * Continuing writes the viewed trainee and asks for the next step by name. The costume form travels
 * only when the draft already names this same trainee: a switch to another trainee clears the old form
 * (R3-06), and the store's exists-join would refuse a card belonging to the previous one.
 */
const form = useForm<{ umamusume_id: number; character_card_id: number | null; next: string }>({
    umamusume_id: props.trainee.id,
    character_card_id: props.selectedCardId,
    next: 'target',
});
const saving = ref(false);

function choose(): void {
    saving.value = true;

    // The write URL is supplied by the server (`career.trainee.store`) so the page never states it as
    // a literal. `next=target` is what makes the PUT land on step 3, where the stored choice is read
    // back from the session draft, so this screen never claims a selection from client memory alone.
    form.put(props.action, {
        preserveScroll: true,
        onFinish: () => {
            saving.value = false;
        },
    });
}

/*
 * The five stat axes, in the order every other screen prints them. The growth row and the base-stat
 * block are both keyed on this list so the two never disagree about which axes exist.
 *
 * Neither value comes from the catalogue: `umamusume` carries no growth column and no base-stat column
 * (`TraineeSelectController` omits the growth filter for exactly that reason). The client prints a
 * five-cell growth row and a real zero as `+0%` (`UMAMUSUME_REFERENCE.md` §1.3.5), but this tool holds
 * no figure for either, so each cell keeps its place and states `N/A` under one reason per block.
 */
const statAxes = ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'] as const;

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
        const entries = group.cells.map(([label, key]) => ({ label, key, letter: letters[key] ?? '-' }));

        entries.sort((a, b) => gradeRank(a.letter) - gradeRank(b.letter));

        return { header: group.header, entries };
    });
});

/** The rest of a family as one muted line, the way `career-profile.html:100-102` draws it. */
function remainingLetters(group: AptitudeGroup): string {
    return group.entries
        .slice(1)
        .map((entry) => `${entry.label} ${entry.letter}`)
        .join(' · ');
}
</script>

<template>
    <SetupLayout :step="2" :visited="props.visited" :saved="props.saved" :choices="props.choices">
        <Head :title="props.trainee.name" />
        <template #eyebrow>Trainee profile</template>
        <!-- The wireframe's 64px portrait is a sibling of the heading (`career-profile.html:57-62`), so it
             goes through the shell's media slot, outside the h1; the empty alt keeps it out of the
             heading's accessible name, and a missing mirror renders nothing (DESIGN.md §4.7). -->
        <template #title-media>
            <span aria-hidden="true"><ArtworkSlot :url="props.trainee.artworkURL" alt="" size="size-16" /></span>
        </template>
        <template #title>
            {{ props.trainee.name }}
            <span v-if="props.trainee.name_ja" lang="ja" class="ml-2 text-base font-normal text-ink-muted">{{ props.trainee.name_ja }}</span>
        </template>

        <!--
          The wireframe's head-band pair (`career-profile.html:64-68`): the way back to the roster and the
          one write on the page. The primary submits the form and asks for the next step by name, so the
          choice is recorded server-side and the target step reads it back from the session draft.
          `Chosen` sits beside the pair as a state readout, not inside either control; the primary is a
          submit button rather than a toggle, so it carries no `aria-pressed`.
        -->
        <template #actions>
            <LinkButton :href="props.rosterUrl" variant="secondary">Choose a different trainee</LinkButton>
            <BaseButton
                id="trainee-select"
                type="button"
                variant="primary"
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

                    <p class="sub mt-2">
                        The aptitudes the source states, one letter per axis. The strongest of each family
                        is bold; it is not a judgement about which distance or style suits a career.
                    </p>

                    <table v-if="aptitudeGroups" class="mt-3 w-full text-sm">
                        <thead>
                            <tr class="border-b border-rule text-left text-xs font-semibold uppercase tracking-wider text-ink-muted">
                                <th v-for="group in aptitudeGroups" :key="group.header" scope="col" class="py-2 pr-3">{{ group.header }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="align-top">
                                <td v-for="group in aptitudeGroups" :key="group.header" class="py-2 pr-3">
                                    <b class="text-ink-strong">{{ group.entries[0].label }} {{ group.entries[0].letter }}</b>
                                    <br>
                                    <span v-if="group.entries.length > 1" class="text-ink-muted">{{ remainingLetters(group) }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="mt-3 text-sm text-ink-muted">
                        <AbsenceValue compact reason="The character document published no aptitude letters for this trainee." />
                    </p>

                    <!-- The page's defence against conflating two scales, in the wireframe's position. -->
                    <p class="note mt-2">
                        <span class="g" aria-hidden="true">○</span>
                        <span class="flex-1">
                            An aptitude letter (S to G) grades how well she handles a surface, distance or running
                            style. A stat grade grades a number out of the run's cap. They are different scales and
                            neither converts into the other, so a strong aptitude letter says nothing about how high
                            a stat will climb.
                        </span>
                    </p>
                </section>

                <section class="card" aria-labelledby="growth">
                    <h2 id="growth">Growth row</h2>

                    <p class="sub mt-2">The place this trainee's training gains are weighted.</p>

                    <div class="tiles mt-2" data-field="growth">
                        <div v-for="axis in statAxes" :key="axis" class="tile">
                            <p class="k">{{ axis }}</p>
                            <p class="v na">N/A</p>
                        </div>
                    </div>

                    <p class="note mt-2">
                        <span class="g" aria-hidden="true">○</span>
                        <span class="flex-1">{{ props.absences.growth.absence }}</span>
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

            <!-- RIGHT: base stats (a stated absence), the forms, the scenario links. -->
            <div class="space-y-4">
                <section class="card" aria-labelledby="base-stats">
                    <h2 id="base-stats">Base stats</h2>

                    <p class="sub mt-2">What the card starts with, before any training.</p>

                    <!-- The rows keep their place and the empty rail the wireframe draws, because a missing row
                         and a missing figure are different claims. No fill is painted: a zero-width bar would
                         read as a measured zero, which is the default the quality bar forbids. -->
                    <div class="mt-2" data-field="base-stats">
                        <div v-for="axis in statAxes" :key="axis" class="srow">
                            <span class="k">{{ axis }}</span>
                            <span class="v">N/A</span>
                            <div class="track" aria-hidden="true"></div>
                        </div>
                    </div>

                    <p class="note mt-2">
                        <span class="g" aria-hidden="true">○</span>
                        <span class="flex-1">{{ props.absences.baseStats.absence }}</span>
                    </p>
                </section>

                <section class="card" aria-labelledby="forms">
                    <h2 id="forms">Costume forms</h2>

                    <p v-if="props.forms.length === 0" class="mt-2 rounded-md border border-dashed border-rule bg-raised p-3 text-sm text-ink-muted">
                        No confirmed costume form. Her unique and starting skills come from a form, so nothing
                        here can be stated until one is confirmed by two sources.
                    </p>

                    <table v-else class="mt-2 w-full text-sm">
                        <thead>
                            <tr class="border-b border-rule text-left text-xs font-semibold uppercase tracking-wider text-ink-muted">
                                <th scope="col" class="py-2 pr-3">Title</th>
                                <th scope="col" class="pr-3">Rarity</th>
                                <th scope="col" class="text-right"><span class="sr-only">Selected</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="card in props.forms"
                                :key="card.id"
                                class="border-b border-rule align-top"
                                :class="card.is_debut_form ? 'is-current' : ''"
                            >
                                <th scope="row" class="py-2 pr-3 text-left font-normal">
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
                                </th>
                                <td class="pr-3"><RarityChip :label="card.rarity_label" :stars="card.rarity_stars" /></td>
                                <td class="text-right">
                                    <span v-if="card.is_debut_form" class="pill pill-positive">Selected</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="note mt-2">
                        <span class="g" aria-hidden="true">○</span>
                        <span class="flex-1">
                            Rarity belongs to a costume form, so it is printed per form and never as a
                            trainee-level badge. A trainee with several forms shows one row each.
                        </span>
                    </p>
                </section>

                <section class="card" aria-labelledby="scenarios">
                    <h2 id="scenarios">Scenarios this trainee is linked to</h2>

                    <p class="sub mt-2">
                        A scenario link decides which scenario cards name this trainee. It does not lock the choice.
                    </p>

                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span v-for="label in props.scenarioLinks" :key="label" class="pill pill-outline">{{ label }}</span>
                    </div>

                    <p class="note mt-2">
                        <span class="g" aria-hidden="true">○</span>
                        <span class="flex-1">
                            No per-trainee link list is carried on this payload, so the scenarios are shown as
                            available rather than as links. The scenario step is where the run's choice is made.
                        </span>
                    </p>
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

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';

interface Reward {
    item: number;
    num_min: number;
    num_max: number;
    chance: string;
}

interface Slot {
    id: number;
    title: string;
    year: number | null;
    month: number | null;
    half: string | null;
    turn: number | null;
    slot_label: string | null;
    tier: string | null;
    grade_code: number | null;
    distance: number | null;
    distance_band: string | null;
    surface: string | null;
    track_name: string | null;
    entries: number | null;
    course: number | null;
    direction: number | null;
    season: number | null;
    factor_effect_1: string | null;
    factor_effect_2: string | null;
    reward_summary: string | null;
    direction_label: string | null;
    course_label: string | null;
    season_label: string | null;
    fans_needed: number | null;
    fans_first: number | null;
    fans_second: number | null;
    fans_third: number | null;
    is_mandatory: boolean;
    is_special_race: boolean;
    did_not_exist: boolean | number | null;
}

interface Fact {
    key: string;
    label: string;
    value: string | null;
    title: string | null;
}

const props = defineProps<{
    slot: Slot;
    facts: Fact[];
}>();

const rewards = computed<Reward[]>(() => {
    if (!props.slot.reward_summary) return [];
    try {
        const parsed = JSON.parse(props.slot.reward_summary);
        return Array.isArray(parsed) ? (parsed as Reward[]) : [];
    } catch {
        return [];
    }
});

const yearLabel = (year: number | null): string => {
    const map: Record<number, string> = { 1: 'Junior', 2: 'Classic', 3: 'Senior', 4: 'Finale' };
    return year === null ? 'N/A' : (map[year] ?? String(year));
};

const distanceText = computed(() => {
    if (props.slot.distance === null) return 'Decided by your most-run types';
    return `${props.slot.distance.toLocaleString('en-US')} m`;
});

const flags = computed(() => [
    { label: 'Mandatory', value: props.slot.is_mandatory },
    { label: 'Special race', value: props.slot.is_special_race },
    { label: 'Did not exist', value: props.slot.did_not_exist },
]);

/*
 * Spark effects are the source's own values: the five stat keys when the effect lands on a stat,
 * and a skill or venue name verbatim otherwise. The stat keys are printed with the client's own
 * capitalisation; anything this page does not recognise is left as the source wrote it.
 */
const SPARK_LABELS: Record<string, string> = {
    speed: 'Speed',
    stamina: 'Stamina',
    power: 'Power',
    guts: 'Guts',
    wit: 'Wit',
};

const sparkLabel = (value: string): string => SPARK_LABELS[value] ?? value;

const sparkText = computed<string>(() =>
    [props.slot.factor_effect_1, props.slot.factor_effect_2]
        .filter((value): value is string => value !== null)
        .map(sparkLabel)
        .join(', '),
);

/*
 * The Rewards card owns the reward payload and prints it as a table with the item-id disclosure.
 * The decoded facts list carries the same `reward_summary` as a JSON string, so the raw row is
 * filtered out here rather than printing the machine payload beside the table that reads it.
 */
const visibleFacts = computed<Fact[]>(() => props.facts.filter((fact) => fact.label !== 'Reward'));
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Database', href: '/database' }, { label: 'Races', href: '/database/races' }, { label: props.slot.title }]" >
        <Head :title="props.slot.title" />
        <template #head>
            <PageHeader eyebrow="Race" :title="props.slot.title" />
        </template>

        <DatabaseNav />

        <div class="mt-4 grid gap-4 g-side">
                <div>
                    <div class="card">
                        <h2>Identity</h2>
                        <table class="t">
                            <tbody>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Title</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">{{ props.slot.title }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Tier</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.tier === null" compact reason="Not recorded" />
                                        <span v-else>{{ props.slot.tier }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Grade code</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.grade_code === null" compact reason="Not recorded" />
                                        <span v-else class="font-mono tabular-nums">{{ props.slot.grade_code }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Distance</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.distance === null" compact reason="Not recorded" />
                                        <span v-else class="font-mono tabular-nums">{{ distanceText }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Distance band</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.distance_band === null" compact reason="Not recorded" />
                                        <span v-else>{{ props.slot.distance_band }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Surface</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.surface === null" compact reason="Not recorded" />
                                        <span v-else>{{ props.slot.surface }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="card mt-4">
                        <h2>Track and conditions</h2>
                        <table class="t">
                            <tbody>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Track name</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.track_name === null" compact reason="No track name recorded for this slot." />
                                        <span v-else>{{ props.slot.track_name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Direction</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.direction === null && props.slot.direction_label === null" compact reason="No direction recorded for this slot." />
                                        <span v-else-if="props.slot.direction_label !== null">{{ props.slot.direction_label }}</span>
                                        <span v-else>Code {{ props.slot.direction }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Course</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.course === null && props.slot.course_label === null" compact reason="No course recorded for this slot." />
                                        <span v-else-if="props.slot.course_label !== null">{{ props.slot.course_label }}</span>
                                        <span v-else>Code {{ props.slot.course }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Season</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.season === null && props.slot.season_label === null" compact reason="No season recorded for this slot." />
                                        <span v-else-if="props.slot.season_label !== null">{{ props.slot.season_label }}</span>
                                        <span v-else>Code {{ props.slot.season }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Entries</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.entries === null" compact reason="No entry count recorded for this slot." />
                                        <span v-else class="font-mono tabular-nums">{{ props.slot.entries }}</span>
                                    </td>
                                </tr>
                                <tr>
                                                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Spark effects</th>
                                                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                                                        <span v-if="sparkText !== ''">{{ sparkText }}</span>
                                                                        <AbsenceValue v-else compact reason="No spark effects recorded for this slot." />
                                                                    </td>
                                                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="card mt-4">
                        <h2>Rewards</h2>
                        <table v-if="rewards.length > 0" class="t">
                            <thead>
                                <tr>
                                    <th scope="col">Item id</th>
                                    <th scope="col" class="n">Min</th>
                                    <th scope="col" class="n">Max</th>
                                    <th scope="col" class="n">Chance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(reward, index) in rewards" :key="index">
                                    <td class="font-mono tabular-nums">{{ reward.item }}</td>
                                    <td class="n font-mono tabular-nums">{{ reward.num_min }}</td>
                                    <td class="n font-mono tabular-nums">{{ reward.num_max }}</td>
                                    <td class="n font-mono tabular-nums">{{ Math.round(Number(reward.chance) * 100) }}%</td>
                                </tr>
                            </tbody>
                        </table>
                        <AbsenceValue v-else compact reason="No reward data recorded for this slot." />
                        <p v-if="rewards.length > 0" class="note mt-2">
                            Rewards are stored by the source's internal item id. This catalogue records no
                            item name for them, so the id is printed rather than a name it cannot verify.
                        </p>
                    </div>
                </div>

                <div>
                    <div class="card">
                        <h2>Fan and payout</h2>
                        <table class="t">
                            <tbody>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Fan gate</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.fans_needed === null" compact reason="Not recorded" />
                                        <span v-else class="font-mono tabular-nums">{{ props.slot.fans_needed?.toLocaleString('en-US') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">1st place</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.fans_first === null" compact reason="Not recorded" />
                                        <span v-else class="font-mono tabular-nums">{{ props.slot.fans_first?.toLocaleString('en-US') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">2nd place</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.fans_second === null" compact reason="Not recorded" />
                                        <span v-else class="font-mono tabular-nums">{{ props.slot.fans_second?.toLocaleString('en-US') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">3rd place</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.fans_third === null" compact reason="Not recorded" />
                                        <span v-else class="font-mono tabular-nums">{{ props.slot.fans_third?.toLocaleString('en-US') }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="card mt-4">
                        <h2>Flags</h2>
                        <table class="t">
                            <tbody>
                                <tr v-for="flag in flags" :key="flag.label">
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">{{ flag.label }}</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="flag.value === null" compact reason="Not recorded" />
                                        <span v-else>{{ flag.value ? 'Yes' : 'No' }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="card mt-4">
                        <h2>Calendar position</h2>
                        <table class="t">
                            <tbody>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Year</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">{{ yearLabel(props.slot.year) }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Month</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.month === null" compact reason="Not recorded" />
                                        <span v-else class="font-mono tabular-nums">{{ props.slot.month }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Half</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.half === null" compact reason="Not recorded" />
                                        <span v-else>{{ props.slot.half }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Turn</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.turn === null" compact reason="Not recorded" />
                                        <span v-else class="font-mono tabular-nums">{{ props.slot.turn }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">Slot label</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="props.slot.slot_label === null" compact reason="Not recorded" />
                                        <span v-else>{{ props.slot.slot_label }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="card mt-4">
                        <h2>Race facts</h2>
                        <table class="t">
                            <tbody>
                                <tr v-for="fact in visibleFacts" :key="fact.key">
                                    <th scope="row" class="w-1/3 px-2 py-2 align-top text-sm text-ink-muted">{{ fact.label }}</th>
                                    <td class="px-2 py-2 align-top text-sm text-ink">
                                        <AbsenceValue v-if="fact.value === null" compact :reason="fact.title ?? 'Not recorded'" />
                                        <span v-else>{{ fact.value }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- GP-2: the enriched columns are not the calendar source's own facts, so the page names
                 the datasets they come from and routes the reader to the ledger where each is dated. -->
            <p class="note mt-4 max-w-3xl">
                <span class="g" aria-hidden="true">○</span>
                <span>
                    Track name, entries, direction, course, season, factor effects and rewards are read
                    from GameTora's racetrack, race, payout and reward datasets.
                    <Link href="/database/sources" class="inline-flex min-h-11 items-center font-medium text-ink-strong underline">Where these datasets come from</Link>
                </span>
            </p>
    </AppLayout>
</template>

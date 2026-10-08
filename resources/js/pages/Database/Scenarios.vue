<script setup lang="ts">
/*
 * The scenario matrix (SCREEN-023, plan §8 D17). The composition matrix in `config/scenarios.php`,
 * printed as it is: the caps, the panels, the widgets, the live date and the documentation marker, with
 * the matrix's own verification date beside the source the config's header cites.
 *
 * The page branches on nothing but resolved values from the matrix. No scenario name reaches the
 * template, so a fifth scenario is one config entry and no edit here (D-240, gate G-33).
 *
 * Every panel renders, on or off. The Scenario Selection cards hide an off panel because a scenario must
 * not claim a system it does not compose; a reference matrix earns its place by stating the matrix, so
 * an off panel is a fact here rather than a silence. The word `off` stays in the text, so the state
 * never rides on colour alone.
 *
 * Density is allowed on this screen only (plan §13, "Deliberately traded").
 */
import AppLayout from '../../layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Cap {
    stat: string;
    bonus: number;
    cap: number;
}

interface Scenario {
    label: string;
    live_on_global: string;
    caps: Cap[];
    widgets: { label: string }[];
    panels: { label: string; enabled: boolean }[];
    partially_documented: boolean;
    notes: string;
}

const props = defineProps<{
    baseCap: number;
    verifiedAt: string;
    scenarios: Record<string, Scenario>;
}>();

const bonusText = (bonus: number): string => (bonus > 0 ? `+${bonus}` : `${bonus}`);
</script>

<template>
    <AppLayout>
        <Head title="Scenarios" />
        <template #title>Scenarios</template>

        <h2 class="text-2xl font-semibold text-ink-strong">Scenario matrix</h2>

        <p class="mt-4 max-w-3xl text-sm text-ink-muted">
            The composition matrix in <code class="font-mono text-xs">config/scenarios.php</code>, printed
            as it is. Caps are the base plus a per-stat bonus, and the base is
            {{ props.baseCap }}. Verified {{ props.verifiedAt }} against the GameTora
            <code class="font-mono text-xs">scenarios.json</code> field <code class="font-mono text-xs">stats</code>
            and the Global prose sources the config header names.
        </p>

        <div class="mt-6 space-y-6">
            <section
                v-for="(scenario, key) in props.scenarios"
                :key="key"
                :aria-labelledby="`scenario-${key}`"
                class="rounded-md border border-rule bg-raised p-5"
            >
                <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2">
                    <h3 :id="`scenario-${key}`" class="text-xl font-semibold text-ink-strong">
                        {{ scenario.label }}
                    </h3>

                    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 text-sm text-ink-muted">
                        <span>
                            Live on [Global]:
                            <time :datetime="scenario.live_on_global">{{ scenario.live_on_global }}</time>
                        </span>

                        <span
                            v-if="scenario.partially_documented"
                            class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong"
                            title="Mechanics are sourced, but the client strings behind the panels are not, so the panels render off."
                        >
                            PARTIALLY DOCUMENTED
                        </span>
                    </div>
                </div>

                <div class="mt-4 grid gap-5 lg:grid-cols-2">
                    <div>
                        <h4 class="text-sm font-semibold text-ink-strong">Caps</h4>

                        <table class="mt-2 w-full text-sm">
                            <caption class="sr-only">
                                {{ scenario.label }} published caps: base, bonus and the sum per stat
                            </caption>
                            <thead>
                                <tr class="border-b border-rule text-left text-xs text-ink-muted">
                                    <th scope="col" class="py-1 pr-3 font-semibold">Stat</th>
                                    <th scope="col" class="py-1 pr-3 font-semibold">Base</th>
                                    <th scope="col" class="py-1 pr-3 font-semibold">Bonus</th>
                                    <th scope="col" class="py-1 font-semibold">Published cap</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-rule">
                                <tr v-for="cap in scenario.caps" :key="cap.stat">
                                    <th scope="row" class="py-1.5 pr-3 text-left font-medium text-ink-strong">
                                        {{ cap.stat }}
                                    </th>
                                    <td class="py-1.5 pr-3 tabular-nums text-ink">{{ props.baseCap }}</td>
                                    <td class="py-1.5 pr-3 tabular-nums text-ink">{{ bonusText(cap.bonus) }}</td>
                                    <td class="py-1.5 tabular-nums font-semibold text-ink-strong">{{ cap.cap }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-semibold text-ink-strong">Widgets</h4>

                            <ul class="mt-2 flex flex-wrap gap-1.5">
                                <li
                                    v-for="widget in scenario.widgets"
                                    :key="widget.label"
                                    class="rounded-full bg-sunken px-2.5 py-1 text-xs text-ink-strong"
                                >
                                    {{ widget.label }}
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-ink-strong">Panels</h4>

                            <ul class="mt-2 divide-y divide-rule border-y border-rule">
                                <li
                                    v-for="panel in scenario.panels"
                                    :key="panel.label"
                                    class="flex items-baseline justify-between gap-3 py-1.5 text-sm"
                                >
                                    <span :class="panel.enabled ? 'text-ink-strong' : 'text-ink-muted'">
                                        {{ panel.label }}
                                    </span>
                                    <span
                                        :class="panel.enabled ? 'font-semibold text-ink-strong' : 'text-ink-muted'"
                                    >
                                        {{ panel.enabled ? 'on' : 'off' }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <p class="mt-4 text-sm text-ink-muted">{{ scenario.notes }}</p>
            </section>
        </div>
    </AppLayout>
</template>

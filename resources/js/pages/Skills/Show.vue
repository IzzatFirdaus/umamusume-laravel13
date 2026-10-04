<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Effect {
    type: number;
    value: number;
}

interface ConditionGroup {
    base_time: number | null;
    condition: string | null;
    precondition: string | null;
    effects: Effect[];
}

interface Skill {
    id: number;
    name: string;
    name_ja: string | null;
    type: string | null;
    sp_cost: number | null;
    is_unique: boolean;
    condition_groups: ConditionGroup[] | null;
    source_url: string | null;
    fetched_at_display: string | null;
}

interface Holder {
    name: string;
    slug: string;
    url: string;
}

const props = defineProps<{
    skill: Skill;
    uniqueHolders: Holder[];
    innateHolders: Holder[];
    awakeningHolders: Holder[];
    eventHolders: Holder[];
}>();

const holderGroups = computed(() => [
    { heading: 'As her unique skill', holders: props.uniqueHolders },
    { heading: 'Among her innate skills', holders: props.innateHolders },
    { heading: 'Among her awakening skills', holders: props.awakeningHolders },
    { heading: 'Among her event skills', holders: props.eventHolders },
]);

const hasAnyHolder = computed(() => holderGroups.value.some((group) => group.holders.length > 0));

const conditions = computed(() => props.skill.condition_groups ?? []);
</script>

<template>
    <AppLayout>
        <Head :title="skill.name" />
        <template #title>{{ skill.name }}</template>

        <div class="flex justify-end">
            <Link href="/skills" class="text-sm text-ink-muted hover:underline">Back to skill search</Link>
        </div>

        <!-- The index's dedupe rule carried over: 18 of the 623 [Global] rows store the same
             string in both name columns, and printing the pair repeats the name. -->
        <p v-if="skill.name_ja !== null && skill.name_ja !== skill.name" lang="ja" class="mt-1 text-sm text-ink-muted">
            {{ skill.name_ja }}
        </p>

        <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-3">
            <div>
                <dt class="text-ink-muted">Type</dt>
                <dd class="text-ink">
                    <template v-if="skill.type">{{ skill.type }}</template>
                    <span v-else title="The source's effect codes give this skill no type word.">N/A</span>
                </dd>
            </div>

            <div>
                <dt class="text-ink-muted">SP cost</dt>
                <dd class="text-ink">
                    <template v-if="skill.sp_cost !== null">{{ skill.sp_cost }} SP</template>
                    <span v-else title="The source states no SP cost for this class of skill.">N/A</span>
                </dd>
            </div>

            <div>
                <dt class="text-ink-muted">Unique</dt>
                <dd class="text-ink">
                    <span
                        v-if="skill.is_unique"
                        class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong"
                        title="Marked from the source's skill class code, not from a card's own skill list."
                    >✦ Unique</span>
                    <template v-else>No</template>
                </dd>
            </div>
        </dl>

        <!-- The mechanics the source states, then the absences it does not. The activation predicate
             and effect vector are the engine's own expressions and codes, not client copy. -->
        <section class="mt-8" aria-labelledby="mechanics">
            <h2 id="mechanics" class="text-lg font-semibold text-ink-strong">Mechanics</h2>

            <template v-if="conditions.length > 0">
                <div v-for="(group, index) in conditions" :key="index" class="mt-3 rounded-md border border-rule bg-raised p-3 text-sm">
                    <h3 v-if="conditions.length > 1" class="text-xs font-bold uppercase tracking-widest text-ink-muted">
                        Activation {{ index + 1 }}
                    </h3>

                    <dl class="grid gap-x-6 gap-y-2 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <dt class="text-ink-muted">Condition</dt>
                            <dd class="font-mono text-xs text-ink">{{ group.condition ?? 'N/A' }}</dd>
                        </div>

                        <div v-if="group.precondition !== null" class="md:col-span-2">
                            <dt class="text-ink-muted">Precondition</dt>
                            <dd class="font-mono text-xs text-ink">{{ group.precondition }}</dd>
                        </div>

                        <div v-if="group.base_time !== null">
                            <dt class="text-ink-muted">Base time</dt>
                            <dd class="text-ink">{{ group.base_time }} ms</dd>
                        </div>
                    </dl>

                    <ul v-if="group.effects.length > 0" class="mt-2 flex flex-wrap gap-2 text-xs">
                        <li v-for="(effect, effectIndex) in group.effects" :key="effectIndex" class="rounded-md bg-sunken px-2 py-1 font-mono text-ink">
                            effect {{ effect.type }}: {{ effect.value > 0 ? '+' : '' }}{{ effect.value }}
                        </li>
                    </ul>
                </div>

                <p class="mt-2 text-xs text-ink-muted">
                    The expressions and effect codes above are the source's own engine data, not client
                    labels. The description is not recorded: no client-string source for it exists
                    (`ADR-0011`, G-SK-20), and no awakening threshold is kept either.
                </p>
            </template>

            <p v-else class="mt-2 text-sm text-ink-muted">
                Activation conditions, effect values and distance or style qualifiers are not
                recorded. This tool's skills source stores no description it may render, which
                `ADR-0011` records, and no awakening thresholds are kept.
            </p>
        </section>

        <!-- The holder lists keep the card's own grouping, because the lists answer different
             questions: the unique list names the skill that belongs to her, the innate list names
             what her kit starts with, and the awakening and event lists name what those routes
             grant. The trainee is the link, once per group however many of her forms carry the skill. -->
        <section class="mt-8" aria-labelledby="held-by">
            <h2 id="held-by" class="text-lg font-semibold text-ink-strong">Held by trainees</h2>

            <p v-if="!hasAnyHolder" class="mt-2 text-sm text-ink-muted">No trainees recorded with this skill.</p>
            <template v-else>
                <template v-for="group in holderGroups" :key="group.heading">
                    <template v-if="group.holders.length > 0">
                        <h3 class="mt-4 text-xs font-bold uppercase tracking-widest text-ink-muted">{{ group.heading }}</h3>
                        <ul class="mt-1">
                            <li v-for="holder in group.holders" :key="holder.slug" class="py-1 text-sm">
                                <a :href="holder.url" class="text-ink-strong underline">{{ holder.name }}</a>
                            </li>
                        </ul>
                    </template>
                </template>
            </template>

            <p class="mt-2 text-xs text-ink-muted">
                From the stored `skills_unique`, `skills_innate`, `skills_awakening` and
                `skills_event` lists on each trainee's costume forms; a form hidden as unconfirmed
                holds nothing here.
            </p>
        </section>

        <!-- D-33 at meta weight: the row's own provenance, named the way a costume form names
             its fetch. A row no fetch wrote states that instead of printing an empty line. -->
        <h2 class="mt-8 text-lg font-semibold text-ink-strong">Provenance</h2>
        <p v-if="skill.source_url" class="mt-2 text-xs text-ink-muted">
            from
            <a :href="skill.source_url" class="text-ink-strong underline">{{ skill.source_url }}</a>
            <template v-if="skill.fetched_at_display"> · read {{ skill.fetched_at_display }}</template>
        </p>
        <p v-else class="mt-2 text-sm text-ink-muted">No fetched source. This record was seeded or entered by hand.</p>
    </AppLayout>
</template>

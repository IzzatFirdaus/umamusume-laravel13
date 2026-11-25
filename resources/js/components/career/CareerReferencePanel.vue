<script setup lang="ts">
/*
 * Published reference for one career. This panel never edits the run: it reads the shared
 * `CareerReference` payload so the Timeline and the Veteran detail screen print the same card,
 * objective, scenario, skill and mechanics facts with the same separation warning.
 */
export interface CareerReferencePayload {
    available: boolean;
    imported: boolean;
    trainee: string | null;
    scenario_key: string | null;
    card: {
        card_id: number;
        title: string | null;
        rarity_label: string | null;
        global_release_date: string | null;
        source_url: string | null;
    } | null;
    profile: {
        aptitudes: { key: string; label: string; letter: string | null }[];
        base_stats: Record<string, number> | null;
        growth_rates: Record<string, number> | null;
    } | null;
    skills: {
        key: string;
        label: string;
        skills: { name: string; sp_cost: number | null; is_unique: boolean; url: string }[];
        unresolved: number;
    }[];
    schedule: {
        trainee: { source: string; milestones: [number, string][] } | null;
        scenario: { source: string; milestones: [number, string][] } | null;
    };
    mechanics: {
        key: string;
        label: string;
        source: string | null;
        published: string | null;
        retrieved: string | null;
        version_note: string | null;
        rework_source: string | null;
        points: string[];
        conflicts: string[];
        ruleset?: { date: string | null; event: string | null; source: string | null } | null;
    }[];
    events: { fixed_events: boolean; note: string };
    coverage: Record<string, boolean>;
    disclaimer: string;
}

const props = defineProps<{ reference: CareerReferencePayload | null }>();

const milestones = (): [number, string][] =>
    [...(props.reference?.schedule.trainee?.milestones ?? []), ...(props.reference?.schedule.scenario?.milestones ?? [])]
        .sort((a, b) => a[0] - b[0]);

const labelForValue = (value: string | null, reason: string): string => value ?? `N/A (${reason})`;

// The Timeline's accessibility gate measures every link in `main`, including links inside a closed
// disclosure. Inline flex with the shared 44px minimum keeps source and skill links tappable there.
const linkClass = 'inline-flex min-h-11 items-center underline';
</script>

<template>
    <details v-if="props.reference?.available" class="card mt-3" data-career-reference>
        <summary class="flex min-h-11 cursor-pointer items-center font-semibold">Published career reference</summary>
        <p class="mt-2 text-sm text-ink-muted">{{ props.reference.disclaimer }}</p>

        <div class="mt-3 grid gap-3 lg:grid-cols-2">
            <section v-if="props.reference.card" aria-label="Card reference" class="rounded-md border border-rule p-3">
                <h3 class="text-sm font-semibold text-ink-strong">Card reference</h3>
                <dl class="mt-2 grid gap-x-4 gap-y-2 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-ink-muted">Form</dt>
                        <dd class="text-ink-strong">{{ labelForValue(props.reference.card.title, 'No form title was published') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Published card ID</dt>
                        <dd class="font-mono text-ink-strong">{{ props.reference.card.card_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Rarity</dt>
                        <dd class="text-ink-strong">{{ labelForValue(props.reference.card.rarity_label, 'No rarity was published') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Global release</dt>
                        <dd class="text-ink-strong">{{ labelForValue(props.reference.card.global_release_date, 'No Global release date was published') }}</dd>
                    </div>
                </dl>
                <p v-if="props.reference.card.source_url" class="mt-2 text-sm">
                    <a :href="props.reference.card.source_url" :class="linkClass">Card source</a>
                </p>
            </section>

            <section v-if="props.reference.profile" aria-label="Published card profile" class="rounded-md border border-rule p-3">
                <h3 class="text-sm font-semibold text-ink-strong">Published card profile</h3>
                <p v-if="props.reference.profile.aptitudes.length" class="mt-2 flex flex-wrap gap-1.5 text-xs">
                    <span
                        v-for="axis in props.reference.profile.aptitudes"
                        :key="axis.key"
                        class="rounded-full bg-sunken px-2 py-0.5 text-ink-strong"
                    >
                        {{ axis.label }}: {{ labelForValue(axis.letter, 'No published aptitude') }}
                    </span>
                </p>
                <p v-if="props.reference.profile.base_stats" class="mt-2 text-sm">
                    <span class="text-ink-muted">Base stats: </span>
                    <span class="font-mono text-ink-strong">{{ JSON.stringify(props.reference.profile.base_stats) }}</span>
                </p>
                <p v-if="props.reference.profile.growth_rates" class="mt-2 text-sm">
                    <span class="text-ink-muted">Growth rates: </span>
                    <span class="font-mono text-ink-strong">{{ JSON.stringify(props.reference.profile.growth_rates) }}</span>
                </p>
            </section>
        </div>

        <section v-if="props.reference.skills.length" aria-label="Published skills" class="mt-3">
            <h3 class="text-sm font-semibold text-ink-strong">Published skills</h3>
            <div class="mt-2 grid gap-3 lg:grid-cols-2">
                <div v-for="group in props.reference.skills" :key="group.key" class="rounded-md border border-rule p-3">
                    <h4 class="text-sm font-semibold text-ink-strong">{{ group.label }}</h4>
                    <ul v-if="group.skills.length" class="mt-2 space-y-1 text-sm">
                        <li v-for="skill in group.skills" :key="skill.url">
                            <a :href="skill.url" :class="linkClass">{{ skill.name }}</a>
                            <span class="text-ink-muted"> ({{ skill.sp_cost === null ? 'SP N/A' : skill.sp_cost + ' SP' }})</span>
                        </li>
                    </ul>
                    <p v-else class="mt-2 text-sm text-ink-muted">No Global skill row resolves for this published list.</p>
                    <p v-if="group.unresolved > 0" class="mt-2 text-xs text-ink-muted">
                        {{ group.unresolved }} published id(s) have no Global catalog row and are not named here.
                    </p>
                </div>
            </div>
        </section>

        <section v-if="milestones().length" aria-label="Objective schedule" class="mt-3">
            <h3 class="text-sm font-semibold text-ink-strong">Objective and scenario schedule</h3>
            <p class="mt-1 text-xs text-ink-muted">Published dates, not this career's results.</p>
            <ul class="mt-2 space-y-1 text-sm">
                <li v-for="(milestone, index) in milestones()" :key="index">Turn {{ milestone[0] }}: {{ milestone[1] }}</li>
            </ul>
            <p class="mt-2 flex flex-wrap gap-3 text-sm">
                <a v-if="props.reference.schedule.trainee" :href="props.reference.schedule.trainee.source" :class="linkClass">Trainee source</a>
                <a v-if="props.reference.schedule.scenario" :href="props.reference.schedule.scenario.source" :class="linkClass">Scenario source</a>
            </p>
        </section>

        <section aria-label="Mechanics reference" class="mt-3">
            <h3 class="text-sm font-semibold text-ink-strong">Mechanics reference</h3>
            <div class="mt-2 grid gap-3 lg:grid-cols-2">
                <article v-for="section in props.reference.mechanics" :key="section.key" class="rounded-md border border-rule p-3">
                    <h4 class="text-sm font-semibold text-ink-strong">{{ section.label }}</h4>
                    <p v-if="section.ruleset?.date" class="mt-1 text-xs text-ink-muted">Global ruleset date: {{ section.ruleset.date }}</p>
                    <p v-if="section.version_note" class="mt-1 text-xs text-ink-muted">{{ section.version_note }}</p>
                    <ul class="mt-2 list-disc space-y-1 pl-4 text-sm">
                        <li v-for="(point, index) in section.points" :key="index">{{ point }}</li>
                    </ul>
                    <ul v-if="section.conflicts.length" class="mt-2 space-y-1 text-xs text-risk">
                        <li v-for="(conflict, index) in section.conflicts" :key="index">Source conflict: {{ conflict }}</li>
                    </ul>
                    <p class="mt-2 flex flex-wrap gap-3 text-xs">
                        <a v-if="section.source" :href="section.source" :class="linkClass">Source</a>
                        <a v-if="section.rework_source" :href="section.rework_source" :class="linkClass">Rework source</a>
                        <span v-if="section.published">Published {{ section.published }}</span>
                        <span v-if="section.retrieved">Retrieved {{ section.retrieved }}</span>
                    </p>
                </article>
            </div>
        </section>

        <p class="mt-3 rounded-md border border-rule bg-sunken p-3 text-xs text-ink-muted">{{ props.reference.events.note }}</p>
    </details>

    <p v-else class="card mt-3 text-sm text-ink-muted">
        No published reference matches this career's card and scenario. The facts below remain
        based only on stored observations.
    </p>
</template>

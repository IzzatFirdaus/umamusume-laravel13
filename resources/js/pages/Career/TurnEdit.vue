<script setup lang="ts">
/**
 * The turn-correction door (`C6b.2`, KI-144).
 *
 * The Cockpit used to host this form inside its `Career tools` disclosure. That disclosure was the
 * interim home for writes the retired record screen owned, and it cannot retire until each write
 * has a door of its own. This is the turn-correction one.
 *
 * The form PUTs to `runs.turns.update` — the route and validator that already own a turn's
 * columns — so the door adds a page and no write path. Validation feedback therefore lands where
 * it always did: `form.errors` from that request.
 */
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

import CareerLayout from '../../layouts/CareerLayout.vue';
import BaseInput from '../../components/Form/BaseInput.vue';
import BaseSelect from '../../components/Form/BaseSelect.vue';
import LinkButton from '../../components/Form/LinkButton.vue';
import PageHeader from '../../components/ui/PageHeader.vue';

const props = defineProps<{
    run: {
        id: number;
        trainee: string;
        trainee_ja: string | null;
        status: string;
        status_label: string;
        scenario_label: string;
        run_url: string;
    };
    turn: {
        id: number;
        turn: number;
        speed: number;
        stamina: number;
        power: number;
        guts: number;
        wit: number;
        sp: number | null;
        energy: number | null;
        energy_state: string;
        energy_band: string | null;
        fans: number | null;
        mood: string | null;
        condition: string | null;
    };
    update_url: string;
    timeline_url: string;
    energy_states: string[];
    moods: string[];
}>();

const str = (value: number | string | null): string => (value === null ? '' : String(value));

/** What this page is for, in one line, under the one `h1`. */
const lede = computed(() => `${props.run.trainee}'s recorded reading for turn ${props.turn.turn}.`);

const form = useForm({
    turn: String(props.turn.turn),
    speed: String(props.turn.speed),
    stamina: String(props.turn.stamina),
    power: String(props.turn.power),
    guts: String(props.turn.guts),
    wit: String(props.turn.wit),
    sp: str(props.turn.sp),
    energy: str(props.turn.energy),
    energy_state: props.turn.energy_state,
    energy_band: props.turn.energy_band ?? '',
    fans: str(props.turn.fans),
    mood: props.turn.mood ?? '',
    condition: props.turn.condition ?? '',
});

/**
 * An empty field is "not recorded" and `0` is a real reading, so the two stay apart rather than
 * being collapsed by `Number('')`. The five stats are required by the request, so they always
 * travel as numbers; the rest travel as `null` when the Trainer cleared them.
 */
const submit = (): void => {
    form
        .transform((data) => ({
            ...data,
            sp: data.sp === '' ? null : Number(data.sp),
            energy: data.energy === '' ? null : Number(data.energy),
            energy_band: data.energy_band === '' ? null : data.energy_band,
            fans: data.fans === '' ? null : Number(data.fans),
            mood: data.mood === '' ? null : data.mood,
            condition: data.condition === '' ? null : data.condition,
        }))
        .put(props.update_url);
};
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :trainee-ja="props.run.trainee_ja"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :status="props.run.status"
        :position="null"
        :run-url="props.run.run_url"
        current="cockpit"
    >
        <Head :title="`Turn ${props.turn.turn}: ${props.run.trainee}`" />

        <template #head>
            <PageHeader eyebrow="Correct turn" :title="`Turn ${props.turn.turn}`" :description="lede">
                <template #actions>
                    <LinkButton :href="props.run.run_url + '/cockpit'" variant="secondary" size="sm">
                        Back to Cockpit
                    </LinkButton>
                </template>
            </PageHeader>
        </template>

        <section aria-labelledby="turn-edit-heading" class="card mt-4">
            <h2 id="turn-edit-heading" class="text-lg font-semibold text-ink-strong">
                Correct turn {{ props.turn.turn }} by hand
            </h2>
            <p class="sub">
                Every field of the turn the app is reading. It writes on submit and shows no preview,
                so use it to fix a mistyped reading rather than to log a new turn.
            </p>

            <div
                v-if="form.hasErrors"
                role="alert"
                tabindex="-1"
                class="mt-3 rounded-md border border-risk bg-raised p-3 text-sm text-risk"
            >
                <p class="font-semibold">The correction was not saved.</p>
                <ul class="mt-1 list-disc pl-5">
                    <li v-for="(message, key) in form.errors" :key="key">{{ message }}</li>
                </ul>
            </div>

            <form class="mt-3 grid grid-cols-2 gap-3 text-sm @4xl:grid-cols-3" @submit.prevent="submit">
                <BaseInput id="turn" v-model="form.turn" type="number" label="Turn" min="1" />
                <BaseInput id="speed" v-model="form.speed" type="number" label="Speed" min="0" />
                <BaseInput id="stamina" v-model="form.stamina" type="number" label="Stamina" min="0" />
                <BaseInput id="power" v-model="form.power" type="number" label="Power" min="0" />
                <BaseInput id="guts" v-model="form.guts" type="number" label="Guts" min="0" />
                <BaseInput id="wit" v-model="form.wit" type="number" label="Wit" min="0" />
                <BaseInput id="sp" v-model="form.sp" type="number" label="Skill points" min="0" />
                <BaseInput id="energy" v-model="form.energy" type="number" label="Energy" min="0" />
                <BaseSelect
                    id="energy_state"
                    v-model="form.energy_state"
                    label="Energy state"
                    :options="props.energy_states.map(state => ({ value: state, label: state }))"
                />
                <BaseInput id="energy_band" v-model="form.energy_band" label="Energy band" />
                <BaseInput id="fans" v-model="form.fans" type="number" label="Fans" min="0" />
                <BaseSelect
                    id="mood"
                    v-model="form.mood"
                    label="Mood"
                    :options="[{ value: '', label: 'Not recorded' }, ...props.moods.map(mood => ({ value: mood, label: mood }))]"
                />
                <BaseInput id="condition" v-model="form.condition" label="Condition" />

                <div class="col-span-2 flex flex-wrap items-center gap-3 @4xl:col-span-3">
                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving…' : 'Save correction' }}
                    </button>
                    <LinkButton :href="props.timeline_url" variant="secondary" size="sm">
                        Career Timeline
                    </LinkButton>
                    <p v-if="form.recentlySuccessful" role="status" class="text-xs text-ink-muted">Saved.</p>
                </div>
            </form>
        </section>
    </CareerLayout>
</template>

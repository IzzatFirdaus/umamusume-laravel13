<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

interface Choice {
    key: string;
    label: string;
    detail?: string | null;
    unverified?: boolean;
    facility_level?: number | null;
    present?: number | null;
}

interface Delta {
    direction: 'up' | 'down';
    text: string;
}

interface ShopItem {
    name: string;
    effect: string;
    cost: number;
    sale?: boolean;
    limited?: boolean;
}

interface ScenarioDef {
    label: string;
    steps: string[];
    panels: Record<string, boolean>;
    shop?: { rotation_turns: number; max_copies_per_item: number; locked_until_debut: boolean };
    shop_items?: ShopItem[];
    team_race?: {
        opponent_count: number;
        circles_guidance: number;
        occurs_every_months: number;
        loss_retryable_with_alarm_clock: boolean;
        opponents: { name: string; tier: string }[];
    };
}

const props = defineProps<{
    // The scenario's resolved config, composed server-side. The component branches on these flags
    // and never on a scenario slug (G-33), and a named default would put a scenario in the view (D-240).
    def: ScenarioDef;
    // Whether the run actually declared a scenario. The baseline key orders the steps either way; this
    // decides only whether the rail may name the scenario out loud (D-220).
    declared: boolean;
    current: string;
    /**
     * The rail's own stages, in order. Not `def.steps`: that is the scenario's turn vocabulary and
     * names stages this rail never lands on, so counting against it made the indicator jump from
     * "Step 2 of 5" to "Step 4 of 5" with no step 3 (D7).
     */
    flow: string[];
    selected: string | null;
    choices: Choice[];
    preview: Delta[];
    previewed: boolean;
    energy: number | null;
    action: string;
}>();

const STEP_LABELS: Record<string, string> = {
    facility: 'Choose facility',
    training: 'Choose activity',
    shop: 'Spend Shop Coins',
    team_race: 'Choose opponent',
    outcome: 'Record outcome',
    skill: 'Review skills',
    confirm: 'Confirm',
};

const form = ref<HTMLFormElement | null>(null);
const group = ref<HTMLDivElement | null>(null);

const index = computed(() => {
    const found = props.flow.indexOf(props.current);

    return found === -1 ? 0 : found;
});

// Absent, not zero: an empty five-cell gauge would say the trainee is exhausted on a run that has
// logged no turn (D-220).
const segments = computed(() =>
    props.energy === null ? null : Math.max(0, Math.min(5, Math.round(props.energy / 20))),
);

// 50 is the only sourced Energy threshold (D-204); the 30 line is this tool's own ruling, which is
// why Danger says so rather than letting a red chip imply it is a game fact.
const band = computed(() => {
    if (props.energy === null) {
        return null;
    }

    if (props.energy > 50) {
        return { word: 'Safe', treat: 'bg-green-tint text-ink' };
    }

    return props.energy >= 30
        ? { word: 'Caution', treat: 'bg-pick text-on-pick' }
        : { word: 'Danger', treat: 'bg-risk text-on-chrome' };
});

function submit(stage: 'preview' | 'confirm'): void {
    if (form.value === null) {
        return;
    }

    const data = new FormData(form.value);
    data.set('stage', stage);

    router.post(props.action, Object.fromEntries(data.entries()));
}

/*
 * A digit typed into the Speed field is a number, not a command; without the guard the first
 * keystroke of "550" would jump the selection to the fifth discipline. The test is by input type,
 * not by tag: focus sits on a radio right after an arrow moves the selection, so excluding every
 * INPUT swallowed the shortcut in the one place it exists.
 *
 * Arrow-key roving is deliberately not reimplemented: it is already the browser's behaviour inside
 * a radio group, and doing it in JS would fight the platform and break in a text field.
 */
const NON_TEXT_TYPES = ['radio', 'checkbox', 'button', 'submit', 'reset', 'image', 'hidden'];

function onKeydown(event: KeyboardEvent): void {
    if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.altKey || group.value === null) {
        return;
    }

    const target = event.target;
    const editing =
        (target instanceof HTMLInputElement && !NON_TEXT_TYPES.includes(target.type))
        || (target instanceof HTMLElement
            && (target.tagName === 'TEXTAREA' || target.tagName === 'SELECT' || target.isContentEditable));

    if (editing) {
        return;
    }

    const radios = () => Array.from(group.value?.querySelectorAll<HTMLInputElement>('input[type="radio"]') ?? []);

    if (/^[1-9]$/.test(event.key)) {
        const option = radios()[Number(event.key) - 1];

        if (option !== undefined) {
            option.checked = true;
            option.focus();
            event.preventDefault();
        }

        return;
    }

    if (event.key === 'Escape') {
        // "Step back" in a server-rendered two-stage flow is a focus move, not an undo: clearing the
        // numbers a Trainer typed would destroy entered work (D-56 sends them back with values intact).
        const anchor = group.value.querySelector<HTMLInputElement>('input:checked') ?? radios()[0] ?? null;
        anchor?.focus();
        event.preventDefault();
    }
}

/*
 * Scoped to the document, not to the form, because that is what the retired `guided-flow.ts` did and
 * the card's own copy advertises the keys to the whole page: a Trainer who presses "3" straight after
 * load has body focus, so a binding on the form would swallow the advertised shortcut exactly when it
 * is most likely to be used. The `group.value === null` guard above is what keeps the listener inert
 * on a step that has no choices.
 */
onMounted(() => document.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
</script>

<template>
    <form ref="form" :action="action" method="POST" class="rounded-md border border-rule bg-panel p-3" @submit.prevent="submit('preview')">
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <div class="flex gap-1.5" role="group" aria-label="Guided turn progress">
                <span
                    v-for="(step, stepIndex) in flow"
                    :key="step"
                    class="h-1.5 w-8 rounded"
                    :class="stepIndex <= index ? 'bg-green' : 'bg-idle'"
                    :title="STEP_LABELS[step] ?? step"
                ></span>
            </div>
            <span class="text-xs font-semibold text-ink-muted">
                Step {{ index + 1 }} of {{ flow.length }} · {{ STEP_LABELS[current] ?? current }}
            </span>
            <span v-if="declared" class="ml-auto text-xs text-ink-muted">{{ def.label }}</span>
        </div>

        <p v-if="choices.length > 0" class="-mt-1 mb-3 text-xs text-ink-muted">
            Keys 1 to {{ choices.length }} choose an activity, arrow keys move between them, Enter
            previews the turn, Escape returns to the choices.
        </p>

        <!-- The radios are the semantic layer and the banner is what a person sees, because §6.10
             says choices are banner buttons and a role="radio" button carries no submitted value.
             1px, not 0: a zero-size box is invisible to every tool that measures visibility,
             Playwright's click included, which would leave the control that holds the rail's state
             untestable. -->
        <div v-if="choices.length > 0" ref="group" class="flex flex-col gap-2" role="radiogroup" aria-label="Turn choice">
            <label v-for="(choice, choiceIndex) in choices" :key="choice.key" class="block">
                <input
                    type="radio"
                    name="choice"
                    :value="choice.key"
                    class="peer size-px opacity-0"
                    :checked="choice.key === selected"
                >
                <span
                    class="flex cursor-pointer items-center gap-3 rounded-md border-2 border-rule bg-raised px-3 py-2.5 text-left
                           hover:border-green-line
                           peer-checked:border-pick-line
                           peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-ring"
                >
                    <span class="grid size-6 shrink-0 place-items-center rounded border border-rule bg-sunken text-xs font-bold text-ink-strong">
                        {{ choiceIndex + 1 }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-base font-bold text-ink-strong">{{ choice.label }}</span>
                        <span
                            v-if="choice.unverified === true"
                            class="ml-1 inline-block rounded border border-down px-1 align-middle text-xs font-bold text-down"
                            title="Not confirmed as the Global client string"
                        >[Unverified]</span>
                        <span v-if="choice.detail" class="block text-xs text-ink-muted">{{ choice.detail }}</span>
                    </span>
                    <span
                        v-if="choice.facility_level !== null && choice.facility_level !== undefined"
                        class="rounded border border-rule px-2 py-0.5 text-xs font-semibold text-ink-muted"
                    >
                        Lv {{ choice.facility_level }}
                    </span>
                    <span
                        v-if="choice.present !== null && choice.present !== undefined"
                        class="flex gap-0.5"
                        role="img"
                        :aria-label="`${choice.present} teammates on this tile`"
                    >
                        <span
                            v-for="cell in 5"
                            :key="cell"
                            class="h-3 w-2"
                            :class="cell - 1 < Number(choice.present) ? 'bg-up' : 'bg-idle'"
                        ></span>
                    </span>
                </span>
            </label>
        </div>

        <!-- The numbers, the mood and the outcome arrive as the slot from the screen that knows the
             route, inside this same form: a radio in the card and a text field in the page must post
             as one submission, the way the Blade's {{ $slot }} did. -->
        <slot />

        <div v-if="preview.length > 0" class="mt-3 rounded-md border border-rule bg-raised p-3">
            <h3 class="mb-2 text-xs font-bold uppercase tracking-widest text-ink-muted">Preview</h3>
            <div class="flex flex-wrap gap-4">
                <!-- Gains are orange and losses blue; green is reserved for actions (§3.3). -->
                <span
                    v-for="(delta, deltaIndex) in preview"
                    :key="deltaIndex"
                    class="font-mono text-sm font-bold tabular-nums"
                    :class="delta.direction === 'down' ? 'text-down' : 'text-up'"
                >
                    {{ delta.text }}
                </span>
            </div>
            <p class="mt-2 text-xs text-ink-muted">
                Recorded as entered. No outcome is projected and no odds are shown, because no source
                publishes them. Anything you did not log is not claimed.
            </p>
        </div>

        <div
            v-if="current === 'shop' && def.panels.shop === true && def.shop"
            class="mt-3 rounded-md border border-rule bg-raised p-3"
        >
            <h3 class="text-xs font-bold uppercase tracking-widest text-ink-muted">Shop</h3>
            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs text-ink-muted">
                <span class="font-semibold text-ink">
                    Rotation resets in {{ def.shop.rotation_turns }} turns
                </span>
                <span>Shop Coins: not yet recorded</span>
                <span>Up to {{ def.shop.max_copies_per_item }} copies of one item</span>
                <span v-if="def.shop.locked_until_debut === true">Locked until debut</span>
            </div>
            <ul class="mt-3 flex flex-col gap-1.5">
                <li
                    v-for="item in def.shop_items ?? []"
                    :key="item.name"
                    class="flex items-baseline justify-between gap-3 rounded-md border border-rule bg-panel px-3 py-2 text-sm"
                >
                    <span class="min-w-0 flex-1">
                        <span class="font-semibold text-ink-strong">{{ item.name }}</span>
                        <span class="block text-xs text-ink-muted">{{ item.effect }}</span>
                    </span>
                    <span class="flex shrink-0 items-center gap-1.5">
                        <span
                            v-if="item.sale === true"
                            class="rounded border border-down px-1.5 text-xs font-bold text-down"
                        >Sale</span>
                        <span
                            v-if="item.limited === true"
                            class="rounded border border-idle px-1.5 text-xs font-bold text-ink-muted"
                        >Limited</span>
                        <span class="font-mono text-sm tabular-nums text-ink-strong">{{ item.cost }}c</span>
                    </span>
                </li>
            </ul>
            <p class="mt-2 text-xs text-ink-muted">
                held: not yet recorded · A multi-turn item cannot be used again while active, and buying a
                weaker effect than the one running overwrites the active one, so the order of two purchases
                is a real, lossy decision.
            </p>
            <p class="mt-1 text-xs text-ink-muted">
                Flags sit where the client puts them: Sale top-left, Limited top-right. This build does not
                model the rotation, so no offer is flagged and the rows above are the item catalogue rather
                than a current lineup.
            </p>
        </div>

        <div
            v-if="current === 'team_race' && def.panels.team_race === true && def.team_race"
            class="mt-3 rounded-md border border-rule bg-raised p-3"
        >
            <h3 class="text-xs font-bold uppercase tracking-widest text-ink-muted">
                Opponent · one of {{ def.team_race.opponent_count }}
            </h3>
            <ul class="mt-2 flex flex-col gap-1.5">
                <li
                    v-for="opponent in def.team_race.opponents"
                    :key="opponent.name"
                    class="flex items-baseline justify-between gap-3 rounded-md border border-rule bg-panel px-3 py-2 text-sm"
                >
                    <span class="min-w-0 flex-1">
                        <span class="font-semibold text-ink-strong">{{ opponent.name }}</span>
                        <span class="block text-xs text-ink-muted">{{ opponent.tier }}</span>
                    </span>
                    <!-- The circles are the client's estimate; this tool records what the Trainer saw
                         and never computes it. -->
                    <span class="shrink-0 text-xs text-ink-muted">circles not yet recorded</span>
                </li>
            </ul>
            <p class="mt-2 rounded-md border border-rule bg-panel px-3 py-2 text-xs text-ink">
                Before you commit, the game shows a circle-based win-odds estimate per category. Source
                guidance: aim for at least {{ def.team_race.circles_guidance }} circles in total as a margin,
                not a win condition, because a loss lowers league rank and beating a stronger team raises it
                further. Opponent names are sample data: the client names its own teams, and this tool has no
                source for them.
            </p>
            <p class="mt-1 text-xs text-ink-muted">
                A Team Race comes every {{ def.team_race.occurs_every_months }} months.
                <template v-if="def.team_race.loss_retryable_with_alarm_clock === true">
                    A loss can be retried with an Alarm Clock item, so a bad race day is not permanent.
                    Older guidance that says it is, is out of date.
                </template>
            </p>
        </div>

        <div class="mt-3 flex flex-wrap items-center justify-end gap-4">
            <div class="flex flex-wrap items-center gap-2.5">
                <span v-if="segments === null" class="font-mono text-sm text-ink-muted">
                    Energy not yet recorded
                </span>
                <template v-else>
                    <span class="flex gap-1" role="img" :aria-label="`Energy ${Number(energy)} of 100`">
                        <span
                            v-for="cell in 5"
                            :key="cell"
                            class="h-2 w-6 rounded-sm"
                            :class="cell - 1 < segments ? 'bg-green' : 'bg-idle'"
                        ></span>
                    </span>
                    <span class="font-mono text-sm tabular-nums text-ink-muted">Energy {{ Number(energy) }}/100</span>
                </template>

                <span
                    v-if="band !== null"
                    class="rounded border border-transparent px-2 py-0.5 text-xs font-bold"
                    :class="band.treat"
                >
                    {{ band.word }}
                </span>
            </div>

            <p v-if="band !== null && band.word === 'Danger'" class="w-full text-right text-xs text-ink-muted">
                Danger starts below 30, and that line is this tool's own ruling: the client publishes no
                Energy threshold below 50.
            </p>

            <p
                v-if="band !== null && Number(energy) < 50"
                class="w-full rounded-md border border-rule bg-raised px-3 py-2 text-right text-xs text-ink"
            >
                <!-- `text-on-green` is named rather than borrowing `text-on-pick`: white on this fill is
                     1.99:1, the pairing D-3 forbids (slice 6 §3). -->
                <span class="mr-1.5 rounded bg-green px-1.5 font-bold text-on-green">Hint</span>
                Wit costs 0 Energy and you are at {{ Number(energy) }}. Rest refills Energy, and a rest can
                backfire, so it is a choice rather than a safe button.
                <span class="block text-ink-muted">GameWith guidance, 2026-09-25.</span>
            </p>

            <div class="flex gap-2">
                <button
                    type="submit"
                    class="rounded-full border-2 border-rule px-4 py-2 text-sm font-bold text-ink-strong"
                    @click.prevent="submit('preview')"
                >
                    Preview this turn
                </button>

                <!-- The gate on confirming is a rendered marker, not a script: the field exists only in a
                     response that already showed a preview. The test is "this response is a preview", not
                     "this response has deltas", because a first turn previews to nothing by arithmetic and
                     gating on the list left every run's first turn uncommittable here (D-1, D-51). -->
                <template v-if="previewed">
                    <input type="hidden" name="previewed" value="1">
                    <button
                        type="submit"
                        class="enamel rounded-full bg-chrome px-5 py-2 text-sm font-bold text-on-chrome"
                        @click.prevent="submit('confirm')"
                    >
                        Confirm turn
                    </button>
                </template>
            </div>
        </div>
    </form>
</template>

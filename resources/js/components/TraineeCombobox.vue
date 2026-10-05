<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';

interface CardRow {
    selectionId: number;
    sourceCardId: number;
    title: string;
    titleKey: string;
    releaseDate: string;
    debut: boolean;
    cardless?: boolean;
}

interface TraineeRow {
    umamusumeId: number;
    trainee: string;
    traineeJa: string | null;
    cards: CardRow[];
}

interface Hit {
    trainee: TraineeRow;
    card: CardRow;
}

interface Option {
    id: string;
    hit: Hit;
    index: number;
}

type Segment =
    | { kind: 'header'; key: string; text: string }
    | { kind: 'divider'; key: string }
    | { kind: 'option'; key: string; option: Option };

const props = defineProps<{
    roster: TraineeRow[];
    initialTraineeId: string;
    initialCardId: string;
    invalid: boolean;
}>();

const emit = defineEmits<{
    (e: 'commit', hit: Hit): void;
    (e: 'clear'): void;
}>();

// Ten rows per band. The default list gets one window per band, because a single slice of the banded
// list shows the recent cards and drops every cardless trainee off the popup on any roster that holds
// a card.
const MAX_VISIBLE = 10;
const BAND_VISIBLE = 10;

const fold = (value: string): string => value.trim().toLowerCase();

// A trainee whose forms are all unconfirmed, or who has none fetched yet, is runnable: it is her
// costume card that is unconfirmed, not her place on the roster. `selectionId: 0` is a placeholder for
// a field that stays empty, never a submitted value.
const cardlessRow = (): CardRow => ({
    selectionId: 0,
    sourceCardId: 0,
    title: 'No costume card confirmed yet',
    titleKey: '',
    releaseDate: '',
    debut: false,
    cardless: true,
});

// Every list this component builds routes through here, so a cardless trainee is a match exactly like
// a card is and the status line cannot count rows the popup does not contain.
const rowsFor = (trainee: TraineeRow): CardRow[] =>
    trainee.cards.length > 0 ? trainee.cards : [cardlessRow()];

const isCardless = (hit: Hit): boolean => hit.card.cardless === true;

function optionLabel(hit: Hit, suffix = ''): string {
    // A middle dot, never a dash: this string is copy a screen reader speaks (R-02, D-79).
    return `${hit.trainee.trainee} · ${hit.card.title}${suffix}`;
}

function optionDetail(hit: Hit): string {
    // The detail drops when empty, which is only a cardless row: there is no release date to state
    // for a card that has not been confirmed.
    return hit.card.debut ? 'debut' : hit.card.releaseDate;
}

function optionAriaLabel(hit: Hit, suffix = ''): string {
    const detail = optionDetail(hit);

    return `${optionLabel(hit, suffix)}${detail === '' ? '' : ` ${detail}`}`;
}

function optionId(hit: Hit): string {
    // A cardless row's `selectionId` is the placeholder 0, so three of them painted at once would all
    // answer to the same id, and aria-activedescendant resolves through the id. She is keyed on her
    // trainee id instead.
    return `trainee-option-${isCardless(hit) ? `u${hit.trainee.umamusumeId}` : hit.card.selectionId}`;
}

// A failed submit comes back with the pair the Trainer chose. Resolved from the ids rather than from a
// label string, so a card the roster no longer carries restores nothing instead of leaving a name over
// a value that no longer matches it.
function restore(): Hit | null {
    for (const trainee of props.roster) {
        for (const card of trainee.cards) {
            if (String(card.selectionId) === props.initialCardId) {
                return { trainee, card };
            }
        }
    }

    if (props.initialCardId !== '') {
        return null;
    }

    const trainee = props.roster.find((row) => String(row.umamusumeId) === props.initialTraineeId);

    return trainee === undefined ? null : { trainee, card: cardlessRow() };
}

const input = ref<HTMLInputElement | null>(null);
const listbox = ref<HTMLUListElement | null>(null);
const restored = restore();
const committed = ref<Hit | null>(restored);
const label = ref(restored === null ? '' : optionLabel(restored));
const text = ref(restored === null ? '' : optionLabel(restored));
const open = ref(false);
const active = ref(-1);
const status = ref('');

// While the field still shows a committed label the text is not a query: filtering
// "Gold Ship · [RUN! RUIN! LAUNCHER!]" as a prefix matches nothing, which would open an empty popup
// over a choice that is already made.
const query = computed(() => (committed.value !== null && text.value === label.value ? '' : text.value));

// Prefix on all three fields: trainee English name, trainee Japanese name, card epithet. The card test
// uses the bracket-stripped key because the verbatim string starts with `[`, so a prefix match on it
// would make `RUN` and every first-letter query return nothing. A trainee whose own name matches shows
// all her forms: `Fuji` asks which Fuji Kiseki card to run.
function matchedCards(trainee: TraineeRow, q: string): CardRow[] | null {
    const folded = fold(q);

    if (folded === '') {
        return null;
    }

    const traineeHit =
        fold(trainee.trainee).startsWith(folded)
        || (trainee.traineeJa !== null && fold(trainee.traineeJa).startsWith(folded));

    if (traineeHit) {
        return rowsFor(trainee);
    }

    const cards = trainee.cards.filter((card) => fold(card.titleKey).startsWith(folded));

    return cards.length > 0 ? cards : null;
}

// The blank guard is load-bearing. `fold` trims, so a query of spaces folds to '' and a trainee with no
// Japanese name recorded folds to '' too. Without the guard, Enter in an untouched field would
// "select" the first such trainee's debut form.
function exactTrainee(q: string): TraineeRow | null {
    const folded = fold(q);

    if (folded === '') {
        return null;
    }

    return props.roster.find(
        (row) => fold(row.trainee) === folded || fold(row.traineeJa ?? '') === folded,
    ) ?? null;
}

const byDate = (a: CardRow, b: CardRow): number => a.releaseDate.localeCompare(b.releaseDate);
const byTrainee = (a: TraineeRow, b: TraineeRow): number => a.trainee.localeCompare(b.trainee);

// Uncapped on purpose: `visible` decides how many rows to build and it needs the real totals to say so.
// Capping here would make the live region read "10 matches" on a roster of 107, which is a claim about
// how many forms exist rather than about how many are on screen.
const matches = computed<Hit[]>(() => {
    const q = query.value;

    if (q.trim() === '') {
        const all = props.roster.flatMap((trainee) =>
            rowsFor(trainee).map((card) => ({ trainee, card })),
        );
        const carded = all
            .filter((hit) => !isCardless(hit))
            .sort((a, b) => b.card.releaseDate.localeCompare(a.card.releaseDate));
        const cardless = all.filter(isCardless).sort((a, b) => byTrainee(a.trainee, b.trainee));

        return [...carded, ...cardless];
    }

    const found = props.roster.flatMap((trainee) =>
        (matchedCards(trainee, q) ?? []).map((card) => ({ trainee, card })),
    );

    const exact = exactTrainee(q);

    // Exact first, then prefix, grouped by trainee inside each band.
    const banded = exact === null
        ? found
        : [
            ...rowsFor(exact).map((card) => ({ trainee: exact, card })),
            ...found.filter((hit) => hit.trainee.umamusumeId !== exact.umamusumeId),
        ];

    return banded.sort((left, right) => {
        const traineeOrder = byTrainee(left.trainee, right.trainee);

        return traineeOrder !== 0 ? traineeOrder : byDate(left.card, right.card);
    });
});

const visible = computed<Hit[]>(() => {
    if (query.value.trim() !== '') {
        return matches.value.slice(0, MAX_VISIBLE);
    }

    return [
        ...matches.value.filter((hit) => !isCardless(hit)).slice(0, BAND_VISIBLE),
        ...matches.value.filter(isCardless).slice(0, BAND_VISIBLE),
    ];
});

const segments = computed<Segment[]>(() => {
    const out: Segment[] = [];
    const options: Option[] = [];
    let lastTrainee: number | null = null;
    let cardedBandSeen = false;
    let dividerPainted = false;

    visible.value.forEach((hit, index) => {
        options.push({ id: optionId(hit), hit, index });

        if (isCardless(hit)) {
            // The seam is painted once, and only behind a confirmed band: on a database holding no
            // costume card at all every row is cardless, and a divider naming "these" would have no
            // other side.
            if (cardedBandSeen && !dividerPainted) {
                dividerPainted = true;
                out.push({ kind: 'divider', key: 'divider' });
            }
        } else {
            cardedBandSeen = true;
        }

        if (hit.trainee.umamusumeId !== lastTrainee) {
            lastTrainee = hit.trainee.umamusumeId;

            out.push({
                kind: 'header',
                key: `header-${hit.trainee.umamusumeId}`,
                text: hit.trainee.traineeJa === null
                    ? hit.trainee.trainee
                    : `${hit.trainee.trainee} ${hit.trainee.traineeJa}`,
            });
        }

        out.push({ kind: 'option', key: options[index].id, option: options[index] });
    });

    return out;
});

const count = computed(() => visible.value.length);

// Resolved here rather than in the template, because a cursor index outlives the keystroke that set
// it: an empty string is the correct value for "no cursor", and a stale index must never name a row
// that is not on screen.
const activeDescendant = computed(() => {
    const hit = active.value >= 0 ? visible.value[active.value] : undefined;

    return hit === undefined ? '' : optionId(hit);
});

function speak(): void {
    if (count.value === 0) {
        status.value = 'No trainee or card found.';

        return;
    }

    // "matches", never "cards match": a cardless trainee is one of the rows this number counts, so the
    // noun has to describe what the list holds as exactly as the number does.
    status.value = matches.value.length > count.value
        ? `${count.value} of ${matches.value.length} (keep typing)`
        : `${matches.value.length} ${matches.value.length === 1 ? 'match' : 'matches'}`;
}

function setOpen(value: boolean): void {
    open.value = value;

    if (!value) {
        // A closed listbox owns no cursor: aria-activedescendant has to name a row a screen reader can
        // reach, and leaving one parked would also decide where the next ArrowDown lands.
        active.value = -1;
    }
}

function commit(hit: Hit, suffix = ''): void {
    committed.value = hit;
    label.value = optionLabel(hit, suffix);
    text.value = label.value;
    status.value = `Selected ${label.value}`;
    emit('commit', hit);
    setOpen(false);

    // Selecting the label is what makes the next keystroke a replacement. Committing with Enter never
    // leaves the field, so the focus handler's own select() does not fire here, and without it a
    // Trainer who picked the wrong form and immediately retypes gets
    // "Gold Ship · [RUN! RUIN! LAUNCHER!]rosy" and a status line reading "No trainee or card found".
    nextTick(() => input.value?.select());
}

function choose(index: number): void {
    const hit = visible.value[index];

    if (hit !== undefined) {
        commit(hit);
    }
}

// Enter with nothing highlighted means the Trainer typed a whole trainee name and wants her. That
// resolves to the debut form, the same default the catalog puts first, and the label says so.
function chooseDebutByName(): boolean {
    const exact = exactTrainee(text.value);

    if (exact === null) {
        return false;
    }

    const cards = rowsFor(exact);
    const debut = cards.find((card) => card.debut) ?? [...cards].sort(byDate)[0];

    if (debut === undefined) {
        return false;
    }

    // `(debut)` only goes on a row that is a card.
    commit({ trainee: exact, card: debut }, debut.cardless === true ? '' : ' (debut)');

    return true;
}

function step(delta: number): void {
    if (count.value === 0) {
        return;
    }

    active.value = active.value < 0
        ? delta > 0 ? 0 : count.value - 1
        : (active.value + delta + count.value) % count.value;

    const hit = visible.value[active.value];

    if (hit !== undefined) {
        nextTick(() => listbox.value?.querySelector(`#${optionId(hit)}`)?.scrollIntoView({ block: 'nearest' }));
    }
}

// Opening from focus lands with no cursor. Without it, a refocused field that already holds a
// selection shows the default list, whose row zero is the newest card in the whole roster, and the
// first Enter re-picks somebody else's form instead of submitting. That pair is self-consistent, so
// StoreTrainingRunRequest's trainee-card join cannot see the disagreement.
function onFocus(): void {
    setOpen(true);
    active.value = -1;
    speak();
}

function onInput(event: Event): void {
    text.value = (event.target as HTMLInputElement).value;

    if (committed.value !== null && text.value !== label.value) {
        // A field reading "Gold" over a pair still pointing at Gold Ship's card would write a run that
        // disagrees with its own input, and the server cannot see it: trainee and card agree with each
        // other. Clearing the pair makes the next submit fail `required` instead.
        committed.value = null;
        label.value = '';
        emit('clear');
    }

    setOpen(true);
    // A typed list parks the cursor on row zero; a list merely opened by focus does not, so Enter stays
    // a submission.
    active.value = count.value > 0 ? 0 : -1;
    speak();
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        setOpen(false);

        return;
    }

    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();

        if (!open.value) {
            // Opening by arrow starts from no cursor, which is what tells `step` this press opens
            // rather than moves. `step` then resolves it per key, because a shared increment from -1
            // lands ArrowUp one short of the last row, skipping the newest card the reopen exists to
            // reach.
            active.value = -1;
            setOpen(true);
            speak();
        }

        step(event.key === 'ArrowDown' ? 1 : -1);

        return;
    }

    if (event.key === 'Enter') {
        if (active.value >= 0 && open.value) {
            event.preventDefault();
            choose(active.value);

            return;
        }

        // With no cursor Enter is the Trainer submitting, so nothing here prevents it unless the typed
        // text names a whole trainee.
        if (chooseDebutByName()) {
            event.preventDefault();
        }
    }
}

// A restore is a selection the Trainer already made, so its pair goes to the form. The live region
// stays empty: announcing it would state a fact about a list nobody asked for, and some AT report a
// polite region's mutation even when nobody opened the popup.
if (restored !== null) {
    emit('commit', restored);
}
</script>

<template>
    <div>
        <input
            id="trainee-combobox"
            ref="input"
            :value="text"
            type="text"
            role="combobox"
            :aria-expanded="open ? 'true' : 'false'"
            aria-controls="trainee-listbox"
            aria-autocomplete="list"
            :aria-activedescendant="activeDescendant"
            aria-label="Trainee or costume card name"
            aria-required="true"
            autocomplete="off"
            placeholder="Trainee or card name"
            class="h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
            :class="invalid ? 'border-risk' : ''"
            @focus="onFocus"
            @blur="setOpen(false)"
            @input="onInput"
            @keydown="onKeydown"
        />

        <ul
            id="trainee-listbox"
            ref="listbox"
            v-show="open"
            role="listbox"
            aria-label="Trainees and costume cards"
            class="mt-1 max-h-72 overflow-y-auto rounded-md border border-rule bg-raised"
        >
            <template v-for="segment in segments" :key="segment.key">
                <!-- Presentation, not role="group": a group names nothing unless it owns its options,
                     and this header is a sibling of hers. The trainee's name goes into each option
                     instead, and aria-hidden keeps the duplicate out of the tree. -->
                <li
                    v-if="segment.kind === 'header'"
                    role="presentation"
                    aria-hidden="true"
                    class="px-3 pt-2 pb-1 text-xs font-semibold text-ink-muted"
                >
                    {{ segment.text }}
                </li>
                <li
                    v-else-if="segment.kind === 'divider'"
                    role="presentation"
                    aria-hidden="true"
                    data-band-divider
                    class="border-t border-rule px-3 pt-3 pb-1 text-xs font-semibold text-ink-muted"
                >
                    No confirmed costume card yet
                </li>
                <li
                    v-else
                    :id="segment.option.id"
                    role="option"
                    :aria-selected="segment.option.index === active ? 'true' : 'false'"
                    :aria-label="optionAriaLabel(segment.option.hit)"
                    class="cursor-pointer px-3 py-1.5 text-sm text-ink"
                    :class="segment.option.index === active ? 'bg-pick text-on-pick' : ''"
                    @mousedown.prevent="choose(segment.option.index)"
                >
                    <span>{{ segment.option.hit.card.title }}</span>
                    <span class="ml-2 text-xs text-ink-muted">{{ optionDetail(segment.option.hit) }}</span>
                </li>
            </template>
        </ul>

        <p aria-live="polite" class="mt-1 text-xs text-ink-muted">{{ status }}</p>
    </div>
</template>

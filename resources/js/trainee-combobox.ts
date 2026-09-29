/*
 * The trainee selector on "New training run" (FR-A-6, ADR-0008).
 *
 * ARIA combobox, not an invented pattern: the input carries role="combobox" with
 * aria-expanded, aria-autocomplete="list" and aria-activedescendant; the popup is a
 * role="listbox"; her cards are the role="option" items, each one naming its own trainee
 * because the header above them is presentation only and deliberately not an option. A
 * heading inside the option list would be a row a Trainer could pick, and a group that owns
 * no option names none. See the WAI-ARIA APG "combobox with list".
 *
 * Everything is additive. The page ships a working native <select>; this module disables
 * it and reveals its own controls only when it runs, so a Trainer with scripting off
 * still creates a run (the same stance guided-flow.ts takes, and the half of the
 * handover TraineeSelectorTest proves from the rendered page the other way round).
 *
 * Filtering is synchronous over ~107 rows already in the document. No debounce, no
 * request, no loading state: a debounce here would be a delay added to a job that was
 * not slow (C-7 as scoped by ADR-0007).
 */

interface CardRow {
    selectionId: number;
    sourceCardId: number;
    title: string;
    titleKey: string;
    releaseDate: string;
    debut: boolean;
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

const MAX_VISIBLE = 10;
const DEFAULT_VISIBLE = 10;

const fold = (value: string): string => value.trim().toLowerCase();

/*
 * Prefix on all three fields the request named: trainee English name, trainee Japanese
 * name, card epithet. The card test uses titleKey, the bracket-stripped form the server
 * builds, because the verbatim string starts with "[" and a prefix match on it would make
 * `RUN` and every first-letter query return nothing. The label still renders the verbatim
 * title.
 *
 * A trainee whose own name matches shows all her forms, not just the matching ones:
 * `Fuji` is a Trainer asking which Fuji Kiseki card to run, which is the shape the
 * request's `Fenomeno` row describes. Null means "this trainee has no matching row", and
 * an empty query is not a match on anything.
 */
const matchesQuery = (row: TraineeRow, query: string): CardRow[] | null => {
    const q = fold(query);

    if (q === '') {
        return null;
    }

    const traineeHit =
        fold(row.trainee).startsWith(q) || (row.traineeJa !== null && fold(row.traineeJa).startsWith(q));

    if (traineeHit) {
        return row.cards;
    }

    const cards = row.cards.filter((card) => fold(card.titleKey).startsWith(q));

    return cards.length > 0 ? cards : null;
};

/*
 * The blank guard is load-bearing. `fold` trims, so a query of spaces folds to '' and
 * `fold(row.traineeJa ?? '')` is also '' for a trainee with no Japanese name recorded.
 * Without the guard, pressing Enter in an untouched field would "select" the first such
 * trainee's debut form.
 */
const exactTraineeName = (rows: TraineeRow[], query: string): TraineeRow | null => {
    const q = fold(query);

    if (q === '') {
        return null;
    }

    return rows.find((row) => fold(row.trainee) === q || fold(row.traineeJa ?? '') === q) ?? null;
};

const byDate = (a: CardRow, b: CardRow): number => a.releaseDate.localeCompare(b.releaseDate);
const byTrainee = (a: TraineeRow, b: TraineeRow): number => a.trainee.localeCompare(b.trainee);

const sortHits = (left: Hit, right: Hit): number => {
    const traineeOrder = byTrainee(left.trainee, right.trainee);

    return traineeOrder !== 0 ? traineeOrder : byDate(left.card, right.card);
};

const collect = (rows: TraineeRow[], query: string): Hit[] => {
    if (query.trim() === '') {
        // Most recently released first. Uncapped here on purpose: `render` decides how many
        // rows to build, and it needs the real total to say so. Capping here would make the
        // live region read "10 cards match" on a roster of 107, which is a claim about how
        // many forms exist rather than about how many are on screen.
        return rows
            .flatMap((trainee) => trainee.cards.map((card) => ({ trainee, card })))
            .sort((a, b) => b.card.releaseDate.localeCompare(a.card.releaseDate));
    }

    const hits = rows.flatMap((trainee) => {
        const cards = matchesQuery(trainee, query);

        return (cards ?? []).map((card) => ({ trainee, card }));
    });

    const exact = exactTraineeName(rows, query);

    // Exact first, then prefix, and grouped by trainee inside each band.
    const banded = exact === null
        ? hits
        : [...exact.cards.map((card) => ({ trainee: exact, card })), ...hits.filter((hit) => hit.trainee.umamusumeId !== exact.umamusumeId)];

    return banded.sort(sortHits);
};

const render = (
    rows: TraineeRow[],
    query: string,
    listbox: HTMLUListElement,
    status: HTMLElement,
    // The boot paints pass this: a polite live region is read out when it changes, so a page
    // load that wrote "10 cards match" before anyone opened the popup was stating a fact about
    // a list the Trainer had not asked for. Every paint that answers a keystroke, a focus or a
    // click still speaks.
    silent = false,
): Hit[] => {
    const matches = collect(rows, query);
    const visible = matches.slice(0, query.trim() === '' ? DEFAULT_VISIBLE : MAX_VISIBLE);

    listbox.textContent = '';

    if (visible.length === 0) {
        if (!silent) {
            status.textContent = 'No trainee or card found.';
        }

        return visible;
    }

    if (!silent) {
        status.textContent =
            matches.length > visible.length
                ? `${visible.length} of ${matches.length} (keep typing)`
                : `${matches.length} ${matches.length === 1 ? 'card' : 'cards'} match`;
    }

    let lastTrainee: number | null = null;

    for (const hit of visible) {
        if (hit.trainee.umamusumeId !== lastTrainee) {
            lastTrainee = hit.trainee.umamusumeId;

            const header = document.createElement('li');
            // Presentation, not `role="group"`. A group has to own its options to name them and
            // this header is a sibling of hers, so the group would be unnamed (ARIA does not take
            // a group's name from its content) and a screen reader moving between her cards would
            // hear a card title with no trainee in it. The header stays as sighted punctuation;
            // the trainee's name goes into each option below, where navigation always picks it up.
            header.setAttribute('role', 'presentation');
            header.setAttribute('data-group-header', '');
            header.className = 'px-3 pt-2 pb-1 text-xs font-semibold text-ink-muted';
            // textContent throughout: titles are source data and must never be parsed as
            // markup, however they were stored.
            header.textContent = hit.trainee.traineeJa === null
                ? hit.trainee.trainee
                : `${hit.trainee.trainee} ${hit.trainee.traineeJa}`;
            listbox.append(header);
        }

        const option = document.createElement('li');
        option.id = `trainee-option-${hit.card.selectionId}`;
        option.setAttribute('role', 'option');
        option.setAttribute('aria-selected', 'false');
        option.dataset.selectionId = String(hit.card.selectionId);
        option.dataset.umamusumeId = String(hit.trainee.umamusumeId);
        option.className = 'cursor-pointer px-3 py-1.5 text-sm text-ink';

        const detail = hit.card.debut ? 'debut' : hit.card.releaseDate;

        const title = document.createElement('span');
        title.textContent = hit.card.title;
        const date = document.createElement('span');
        date.className = 'ml-2 text-xs text-ink-muted';
        date.textContent = detail;

        // Whose card this is, in the option's own accessible text: the header above it is
        // presentation now. A middle dot, not a dash, because an accessible name is copy a
        // screen reader speaks and R-02 and D-79 keep the dash out of copy.
        option.setAttribute('aria-label', `${hit.trainee.trainee} · ${hit.card.title} ${detail}`);

        option.append(title, date);
        listbox.append(option);
    }

    return visible;
};

export const initTraineeCombobox = (): void => {
    const root = document.querySelector<HTMLElement>('[data-combobox]');
    const input = document.querySelector<HTMLInputElement>('[data-combobox-input]');
    const listbox = document.querySelector<HTMLUListElement>('[data-combobox-listbox]');
    const status = document.querySelector<HTMLElement>('[data-combobox-status]');
    const fallback = document.querySelector<HTMLSelectElement>('[data-combobox-fallback]');
    const cardField = document.querySelector<HTMLInputElement>('[data-combobox-card-id]');
    const traineeField = document.querySelector<HTMLInputElement>('[data-combobox-umamusume-id]');
    const roster = document.getElementById('trainee-roster');

    // Every half of the DOM contract has to be present, or this is not the page the module
    // was written for and the native select stays exactly as the server sent it. Spelled
    // as explicit null checks rather than a negated conjunction so the narrowing below is
    // unambiguous: `input`, `listbox` and the rest are used unguarded from here on.
    if (
        root === null
        || input === null
        || listbox === null
        || status === null
        || fallback === null
        || cardField === null
        || traineeField === null
        || roster === null
        || roster.textContent === null
        || roster.textContent === ''
    ) {
        return;
    }

    const rosterText = roster.textContent;
    let rows: TraineeRow[];
    let visible: Hit[] = [];

    /*
     * The payload check is the whole shape, not just its syntax. `JSON.parse` accepts a string,
     * a number, or an object that is not a list of trainees, and the cast below keeps that
     * invisible to TypeScript until the first `flatMap` throws. So the array guard and the first
     * paint both run in here, ahead of the handover: hand the form over on a payload that cannot
     * paint and the page keeps neither picker, because the native select is disabled and the
     * hidden pair is enabled and empty. An empty roster is the same case in a different shape, so
     * the working select is left exactly as the server sent it. Silent, because the Trainer has
     * not opened the list yet and this is the page speaking.
     */
    try {
        rows = JSON.parse(rosterText) as TraineeRow[];

        if (!Array.isArray(rows) || rows.length === 0) {
            return;
        }

        visible = render(rows, '', listbox, status, true);
    } catch {
        return; // leave the native select alone: a bad payload must not break the form
    }

    let active = -1;
    let open = false;

    /*
     * True while the field shows a selection label rather than a query in progress. A
     * label is not a search string: filtering "Gold Ship · [RUN! RUIN! LAUNCHER!]" as a
     * prefix matches nothing, so reopening the field would show an empty popup over a
     * choice that is already made. While it is set, the list shows the default rows and
     * the text is preselected so the first keystroke replaces it.
     */
    let chosen = false;

    // The label the last `commit` wrote, so an edit away from it can be recognised as one.
    let label = '';

    root.classList.remove('hidden');

    /*
     * The handover, in one block so it cannot half-apply: the fallback select stops
     * submitting and the two hidden fields start. Both carry name="umamusume_id", and a
     * display:none control still submits, so if this pair is split across two places a
     * later edit can leave both enabled and the empty hidden value wins the POST. It sits
     * below the first paint for the same reason in miniature: nothing is taken over from a
     * payload that has not proven it can be drawn.
     */
    fallback.disabled = true;
    traineeField.disabled = false;
    cardField.disabled = false;

    const setOpen = (value: boolean): void => {
        open = value;
        listbox.hidden = !value;
        input.setAttribute('aria-expanded', String(value));

        if (!value) {
            /*
             * A closed listbox owns no cursor. Escape, blur and commit all leave through here,
             * and leaving `aria-activedescendant` pointing at a row inside a hidden listbox is
             * the ARIA violation: the active descendant has to be reachable. It also fixes where
             * the next ArrowDown lands, because `refresh()` parks the cursor on row zero and the
             * arrow path adds one.
             */
            active = -1;
            paintActive();
        }
    };

    const options = (): HTMLLIElement[] =>
        Array.from<HTMLLIElement>(listbox.querySelectorAll('li[role="option"]'));

    const paintActive = (): void => {
        options().forEach((option, index) => {
            const live = index === active;

            option.setAttribute('aria-selected', String(live));
            // aria-activedescendant tells a screen reader where the cursor is; the gold
            // selection pair is what tells a sighted keyboard Trainer the same thing
            // (G-11). Both classes come from the theme, and both are already in the
            // compiled stylesheet from Blade sources, which is why setting them here is
            // safe even though Tailwind does not scan this file.
            option.classList.toggle('bg-pick', live);
            option.classList.toggle('text-on-pick', live);
        });

        input.setAttribute('aria-activedescendant', active >= 0 ? options()[active]?.id ?? '' : '');
    };

    const refresh = (silent = false): void => {
        visible = render(rows, chosen ? '' : input.value, listbox, status, silent);
        active = visible.length > 0 ? 0 : -1;
        paintActive();
    };

    /*
     * The value that reaches the server is the local `character_cards.id`, which is what
     * StoreTrainingRunRequest's `exists` rule reads. `sourceCardId` is carried in the
     * payload for matching and display only and never lands in a submitted field.
     */
    const commit = (trainee: TraineeRow, card: CardRow, suffix = ''): void => {
        traineeField.value = String(trainee.umamusumeId);
        cardField.value = String(card.selectionId);
        // A middle dot, not an em dash: R-02 and D-79 keep the dash out of shipped copy,
        // and the label a failed submit redelivers is built the same way server-side.
        const committed = `${trainee.trainee} · ${card.title}${suffix}`;

        label = committed;
        input.value = committed;
        status.textContent = `Selected ${committed}`;
        chosen = true;
        setOpen(false);
    };

    const choose = (index: number): void => {
        const hit = visible[index];

        if (hit !== undefined) {
            commit(hit.trainee, hit.card);
        }
    };

    /*
     * Enter with nothing highlighted means the Trainer typed a whole trainee name and wants
     * her. That resolves to the debut form, the same default the catalog puts first, and the
     * label says so.
     */
    const chooseDebutByTraineeName = (): boolean => {
        const exact = exactTraineeName(rows, input.value);

        if (exact === null) {
            return false;
        }

        const debut = exact.cards.find((card) => card.debut) ?? [...exact.cards].sort(byDate)[0];

        if (debut === undefined) {
            return false;
        }

        commit(exact, debut, ' (debut)');

        return true;
    };

    input.addEventListener('focus', (): void => {
        refresh();
        /*
         * Open with no cursor, which is what the APG says for a combobox with a listbox, and for
         * one reason in particular here: a refocused field that already holds a selection shows the
         * default list, whose row zero is the newest card in the whole roster. Leave the cursor on
         * it and the Enter a Trainer presses meaning "submit" silently re-picks somebody else's
         * form. The pair it writes is self-consistent, so the server cannot catch it.
         */
        active = -1;
        paintActive();
        setOpen(true);
        // A preselected label means the next keystroke replaces it instead of appending to
        // it, which is what keeps `chosen` from turning the field into a stuck filter.
        input.select();
    });

    input.addEventListener('input', (): void => {
        if (chosen && input.value !== label) {
            /*
             * The text stopped naming the row that was picked. A field reading "Gold" over a
             * hidden pair still pointing at Gold Ship's launcher card would create a run
             * describing something its own input no longer says, and the server cannot catch
             * it: trainee and card agree with each other, they only disagree with the
             * Trainer. So the pair goes with the label, and an empty `umamusume_id` fails
             * `required` on the round trip rather than writing the wrong run.
             */
            traineeField.value = '';
            cardField.value = '';
            chosen = false;
        }

        refresh();
        setOpen(true);
    });

    input.addEventListener('blur', (): void => {
        // One frame later, so a click on an option lands before the list goes away.
        window.setTimeout(() => setOpen(false), 120);
    });

    input.addEventListener('keydown', (event: KeyboardEvent): void => {
        if (event.key === 'Escape') {
            setOpen(false);

            return;
        }

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();

            if (!open) {
                refresh();
                // Same rule as focus: a list that was closed has no cursor, so the increment
                // below lands on row zero. `refresh()` alone would leave it on row zero and the
                // increment would land on row one, which is a row the Trainer never asked for.
                active = -1;
                setOpen(true);
            }

            const count = options().length;

            if (count === 0) {
                return;
            }

            active = event.key === 'ArrowDown' ? (active + 1) % count : (active - 1 + count) % count;
            paintActive();
            options()[active]?.scrollIntoView({ block: 'nearest' });

            return;
        }

        if (event.key === 'Enter') {
            if (active >= 0 && open) {
                event.preventDefault();
                choose(active);

                return;
            }

            if (chooseDebutByTraineeName()) {
                event.preventDefault();
            }
        }
    });

    listbox.addEventListener('mousedown', (event: MouseEvent): void => {
        const option = event.target instanceof Element ? event.target.closest('li[role="option"]') : null;

        if (option instanceof HTMLLIElement && option.dataset.selectionId !== undefined) {
            event.preventDefault();
            choose(options().indexOf(option));
        }
    });

    if (input.value !== '') {
        /*
         * A failed submit came back with a selection already made: name it in the hidden
         * fields too, or the visible label and the submitted value disagree. The server
         * writes `trainee · title` into the field (TrainingRunController::selectedCardLabel),
         * so that exact string is what identifies the row here. A label the server wrote for
         * a card the payload no longer carries matches nothing, and the hidden fields stay
         * as the markup left them.
         */
        const restored = rows
            .flatMap((trainee) => trainee.cards.map((card) => ({ trainee, card })))
            .find((hit) => input.value === `${hit.trainee.trainee} · ${hit.card.title}`);

        if (restored !== undefined) {
            label = input.value;
            traineeField.value = String(restored.trainee.umamusumeId);
            cardField.value = String(restored.card.selectionId);
            chosen = true;
        }

        refresh(true);
    }
};

initTraineeCombobox();

/*
 * The keyboard path for the guided turn rail (D-55, gate G-11).
 *
 * Only two of D-55's three clauses need a script, and what is NOT here matters as much as
 * what is. Arrow-key roving is already the browser's behaviour inside a radio group: the
 * checked option is the tabbable one, and the arrows move selection with focus. Reimplement
 * that in JS would fight the platform, duplicate the one thing it gets right for free, and
 * get it wrong in a text field. So there is no roving-tabindex code in this file; the arrow
 * half of the rule is met by the element, and it is verified with real keypresses in the
 * browser pass rather than asserted here.
 *
 * Everything stays additive. With scripting unavailable the rail is still completable: Tab
 * reaches every control, Enter activates the first footer button, which is the preview
 * stage, and nothing writes on a step that must not write (D-51).
 */

const groupSelector = '[role="radiogroup"][aria-label="Turn choice"]';

const choiceGroup = (): HTMLElement | null => document.querySelector<HTMLElement>(groupSelector);

const choiceInputs = (group: HTMLElement): HTMLInputElement[] =>
    Array.from(group.querySelectorAll<HTMLInputElement>('input[type="radio"]'));

/*
 * A digit typed into the Speed field is a number, not a command. Without this guard the
 * first keystroke of "550" would jump the selection to the fifth discipline, which is a
 * worse bug than the shortcut is worth.
 *
 * The test is by entry type, not by tag. A first draft asked whether the target was an
 * INPUT, which is true of the radios too, and the browser pass caught it: focus sits on a
 * radio immediately after an arrow key moves the selection, so the guard swallowed the
 * number shortcut in the one place it exists. Radio, checkbox, button and submit inputs
 * hold no text and must not block it.
 */
const nonTextInputTypes = ['radio', 'checkbox', 'button', 'submit', 'reset', 'image', 'hidden'];

const isEditing = (target: EventTarget | null): boolean => {
    if (!(target instanceof HTMLElement)) {
        return false;
    }

    if (target instanceof HTMLInputElement) {
        return !nonTextInputTypes.includes(target.type);
    }

    return ['TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable;
};

document.addEventListener('keydown', (event: KeyboardEvent): void => {
    if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.altKey) {
        return;
    }

    const group = choiceGroup();

    if (group === null || isEditing(event.target)) {
        return;
    }

    // D-55 names 1-5 for the five disciplines. The rail also offers Rest and a Mood
    // adjustment, and a shortcut that stops at five would leave the last two options
    // pointer-only, so the binding runs to the whole group.
    if (/^[1-9]$/.test(event.key)) {
        const option = choiceInputs(group)[Number(event.key) - 1];

        if (option !== undefined) {
            option.checked = true;
            option.focus();
            event.preventDefault();
        }

        return;
    }

    if (event.key === 'Escape') {
        /*
         * "Escape steps back" in a two-stage server-rendered flow is a focus move, not an
         * undo. Returning to the choice group is the step back; clearing numbers a Trainer
         * has typed would destroy entered work on a keypress, and D-56 sends a Trainer back
         * to the step that needs fixing with their values intact.
         */
        const anchor = group.querySelector<HTMLInputElement>('input:checked')
            ?? choiceInputs(group)[0]
            ?? null;

        anchor?.focus();
        event.preventDefault();
    }
});

/**
 * The ancestry sentence every filed Veteran repeats, and the split that takes it back off the card.
 *
 * `RecordVeteran` writes a note that stands on its own: a short clause about how the career ended, followed
 * by the sentence saying the tracker never saw a Legacy Select block for the run. One record has to be
 * self-contained, so the sentence is stored. On a list of seven the same sentence appears seven times and
 * buries the clause that is actually about that career (`SCREEN-021`, Law 21 Occam's).
 *
 * Both surfaces that print a Veteran's note read this split rather than each holding its own copy of the
 * sentence, so a rewording of it cannot leave the library stripping one string and the comparison printing
 * another. It is compared word for word, never as a pattern: a note the Trainer wrote differently renders
 * in full, because a sentence that is close to this one is not this one.
 */
export const VETERAN_ANCESTRY_NOTE =
    'Legacy ancestry unrecorded: the tracker prints no Legacy Select block for this run, so the two parents, the four grandparents, their Sparks and their star ranks are unknown and the diagram is left empty.';

/**
 * One career's own clause: its notes with the shared ancestry sentence taken off.
 *
 * Returns null when there is no note at all, or when a note held nothing but the shared sentence, so a
 * card never renders an empty paragraph where the sentence used to sit.
 */
export function veteranResultClause(notes: string | null): string | null {
    if (notes === null) {
        return null;
    }

    const own = notes.includes(VETERAN_ANCESTRY_NOTE)
        ? notes.replace(VETERAN_ANCESTRY_NOTE, '').trim()
        : notes.trim();

    return own === '' ? null : own;
}

/** Whether a stored note carries the shared sentence, so a list can state it once above the cards. */
export function carriesAncestryNote(notes: string | null): boolean {
    return (notes ?? '').includes(VETERAN_ANCESTRY_NOTE);
}

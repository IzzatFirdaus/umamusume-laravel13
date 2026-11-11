/*
 * A race-aptitude read, stated as a fact the Trainer weighs and never as a gate.
 *
 * The Global client grades a trainee's surface and distance aptitude A-G. A grade of C or below is a
 * poor fit for THIS trainee — the game still lets her enter, and she still can win — but the row
 * saying so is decision support the grid owes a Trainer who will otherwise discover it after the
 * entry. `UMAMUSUME_REFERENCE.md` §1.4 is the source for the letters; the threshold is a display
 * choice, one line in one file, and FR-F-3's rule that the advisor states facts rather than counsel is
 * why the chip names the grade and crosses nothing out.
 */

/** Grades at or below this letter read as a poor fit. One line; the maintainer may prefer B or D. */
export const POOR_FIT_THRESHOLD = 'C' as const;

const LETTER_ORDER = ['A', 'B', 'C', 'D', 'E', 'F', 'G'] as const;

/**
 * Whether an aptitude letter grades this trainee as a poor fit on that axis. An absent letter is
 * never a judgement: an unrecorded aptitude is a gap, not a D.
 */
export function isPoorFit(aptitude: string | null | undefined): boolean {
    if (aptitude === null || aptitude === undefined) {
        return false;
    }

    const index = LETTER_ORDER.indexOf(aptitude.toUpperCase() as (typeof LETTER_ORDER)[number]);

    return index >= LETTER_ORDER.indexOf(POOR_FIT_THRESHOLD);
}

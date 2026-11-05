/**
 * How a skill row's description reaches a screen, in one place.
 *
 * Two conditions render `No client string.` and they are not the same absence:
 *
 * - `description === null` — the source records no `desc_en` for this row.
 * - `releaseStatus === JapanOnly` — the source records one on some rows, but it carries the same
 *   third-party status as `name_en`: both fields are present on exactly the same 355 rows of the
 *   export, so the Air Messiah test (`UMAMUSUME_REFERENCE.md` §2.7, an English string on a unit with
 *   no `start_en` is a third-party label rather than `[Global]` client text) applies to the
 *   description as much as to the name. The controller already nulls `description` for those rows at
 *   the payload edge, so this second branch is the guard for any future caller that passes a raw row.
 *
 * The gate is on the row's `releaseStatus`, never on the scope the row was reached from. Scope is a
 * visibility control; gating the string on it would let `?scope=all` leak what `?scope=japan_only`
 * refuses for the same row.
 */
export const NO_CLIENT_STRING = 'No client string.';

export interface DescribedSkill {
    description: string | null;
    releaseStatus: string | null;
}

/** The description to print, or the named absence. */
export function displayDescription(skill: DescribedSkill): string {
    if (skill.releaseStatus === 'JapanOnly' || skill.description === null) {
        return NO_CLIENT_STRING;
    }

    return skill.description;
}

/**
 * Whether a string is Japanese enough to carry `lang="ja"`.
 *
 * WCAG 3.1.2 is content-based: the attribute must describe the string's actual language. 73 of the
 * 1,325 JapanOnly rows carry a Latin-script `jpname` (`Ding Dong Boo`, `BLAZING WOLF`,
 * `Danser le présent`) because the client itself set those names in Latin script, so a blanket
 * `lang="ja"` would assert a false fact on them. Any CJK codepoint means some of the string is
 * Japanese; none means none of it is.
 *
 * Range: hiragana 3040-309F, katakana 30A0-30FF, CJK unified ideographs 4E00-9FFF, the iteration
 * marks 3005 and 30FC, and halfwidth katakana FF66-FF9D.
 */
const CJK = /[\u3040-\u30FF\u4E00-\u9FFF\u3005\u30FC\uFF66-\uFF9D]/;

/** `'ja'` when the string contains Japanese script, otherwise `null` (inherit the node's language). */
export function langOf(text: string | null): 'ja' | null {
    return text !== null && CJK.test(text) ? 'ja' : null;
}

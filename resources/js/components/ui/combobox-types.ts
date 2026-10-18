/**
 * The combobox option shape, in a plain module so `tsc --noEmit` can see it.
 *
 * A type exported from `<script setup>` resolves to a generic `*.vue` module for the type checker and
 * fails with TS2614 when a fixture imports it — the same lesson that produced `data-table-types.ts`
 * in slice 1.5.
 */
export interface ComboboxOption {
    /** The value committed when this option is chosen. */
    value: string | number;
    /** The primary text: also what the input shows once chosen. */
    label: string;
    /** Optional second line, e.g. a Japanese name or a card title. */
    sublabel?: string;
    /**
     * The language of `label` and `sublabel`, so a Japanese name is announced in Japanese rather than
     * read out with an English voice. `tagName` follows the same convention as the rest of the app.
     */
    lang?: string;
}

/*
 * The two fixture pages install a global on `window` so the spec can drive the mounted component without
 * remounting it: `ancestry-node-picker.html` exposes `__setModel`, `provenance-badge.html` exposes
 * `__setBadgeState`. Both fixtures assign them (`tests/browser/fixtures/*.ts`) and both specs call them
 * (`ancestry-node-picker.spec.ts:70`, `provenance-badge.spec.ts:68,73`), and until this file existed
 * neither side type-checked: the specs were outside `tsconfig.json`'s scope (KNOWN-ISSUES.md KI-93), and
 * the assignment inside the fixture read as a property that does not exist on `Window`.
 *
 * Declared required rather than optional, because these are fixture pages: the globals exist for the whole
 * life of the document, and the specs only call them after navigating to one of those two pages. An
 * optional declaration would push a non-null assertion onto every call site to say the same thing.
 */
interface Window {
    __setModel: (next: string) => void;
    __setBadgeState: (next: string) => void;
}

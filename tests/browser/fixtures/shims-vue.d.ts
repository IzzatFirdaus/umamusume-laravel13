/*
 * The two fixture modules render real single-file components, so they import `.vue` files directly
 * (`fixtures/ancestry-node-picker.ts`, `fixtures/provenance-badge.ts`). Nothing else in the repository
 * does: no `.ts` file under `resources/js` imports a `.vue`, which is why `npm run typecheck` has passed
 * for years without a declaration for the extension. Vite resolves those imports through the Vue plugin at
 * run time, and this is the type-layer counterpart so the fixtures can be checked at all.
 *
 * The props are deliberately loose. Without `vue-tsc` a `.d.ts` cannot know a component's real prop
 * signature, and the fixtures pass real props (`label`, `sparks`, `probability`, `options`, `modelValue`,
 * `onUpdate:modelValue`). A strict shim would reject them for being undeclared rather than for being wrong,
 * which is noise, not a check. The ceiling: a typo inside a fixture's props is not caught here — the
 * browser run is what catches it. Close this gap with `vue-tsc` if the fixtures ever grow.
 */
declare module '*.vue' {
    import type { DefineComponent } from 'vue';

    const component: DefineComponent<Record<string, unknown>, Record<string, unknown>, unknown>;

    export default component;
}

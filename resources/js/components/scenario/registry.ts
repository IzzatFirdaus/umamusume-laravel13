import type { Component } from 'vue';

/*
 * The scenario panel registry (plan §9 E1).
 *
 * A `Map` rather than an object literal, because the registration order is part of the contract:
 * E3 registers the Unity Cup renderers and E4 the Trackblazer ones, and a later entry for the same
 * key must win. A `Map` also lets a test ask what is registered without importing every renderer.
 *
 * **The shell owns the fallback chain, not this module.** `ScenarioPanel.vue` resolves
 * `resolve(key) ?? ResourceMeter ?? WidgetFallback` for a widget and `resolve(flag) ??
 * WidgetFallback` for a panel flag. Keeping the chain in one place is what makes it testable as one
 * behaviour instead of one behaviour per key.
 *
 * Registrations live in `ScenarioPanel.vue`, which imports the renderers and names the key each one
 * draws. E2's `career_goals` is the first entry; E3 and E4 add theirs beside it. E1 shipped the Map
 * empty, which is why every widget and flag resolved to the meter or the fallback until then.
 */
const renderers = new Map<string, Component>();

/**
 * Register the component that draws one widget or panel key.
 *
 * Keys are the matrix's own: a `widgets[]` entry or a `panels` flag from `config/scenarios.php`.
 * A scenario name is never a key here, which is what keeps a fifth scenario a config-only change
 * (gate G-33).
 */
export function register(key: string, component: Component): void {
    renderers.set(key, component);
}

/**
 * The component registered for a key, or `undefined` when this build cannot draw it.
 *
 * `undefined` is the signal the shell falls back on. It is deliberately not an error and not a
 * blank: an unknown key is a real state the screen draws, so the seam reports it rather than
 * throwing inside a render.
 */
export function resolve(key: string): Component | undefined {
    return renderers.get(key);
}

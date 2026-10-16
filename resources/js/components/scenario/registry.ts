import type { Component } from 'vue';

/*
 * The scenario panel registry (plan §9 E1).
 *
 * **Two maps, not one.** A widget key and a panel flag are different shapes with different props: a
 * widget receives `scenario.widget_values[key]` and renders one reading, a panel receives the whole
 * `scenario` section and composes several. A single map let a config entry that names a widget
 * exactly like a registered panel flag resolve the panel component against widget props — which
 * throws inside the render, the defect `f523dcd` removed from the shell's own registrations. Splitting
 * the maps makes that unrepresentable: `resolveWidget` can never return a panel component and vice
 * versa, whatever the matrix names.
 *
 * A `Map` rather than an object literal, because the registration order is part of the contract: E3
 * registers the Unity Cup renderers and E4 the Trackblazer ones, and a later entry for the same key
 * must win. A `Map` also lets a test ask what is registered without importing every renderer.
 *
 * **The shell owns the fallback chain, not this module.** `ScenarioPanel.vue` resolves
 * `resolveWidget(key) ?? ResourceMeter ?? WidgetFallback` for a widget and `resolvePanel(flag) ??
 * WidgetFallback` for a panel flag. Keeping the chain in one place is what makes it testable as one
 * behaviour instead of one behaviour per key.
 *
 * Registrations live in `ScenarioPanel.vue`, which imports the renderers and names the key each one
 * draws. E2's `career_goals` is the first entry; E3 and E4 add theirs beside it.
 */
const widgetRenderers = new Map<string, Component>();
const panelRenderers = new Map<string, Component>();

/**
 * Register the component that draws one **widget** key — a `widgets[]` entry from
 * `config/scenarios.php` that renders a single reading.
 *
 * A scenario name is never a key here, which is what keeps a fifth scenario a config-only change
 * (gate G-33).
 */
export function registerWidget(key: string, component: Component): void {
    widgetRenderers.set(key, component);
}

/**
 * Register the component that draws one **panel** flag — a `panels` flag from `config/scenarios.php`
 * that composes several widgets and the §49 sections.
 */
export function registerPanel(flag: string, component: Component): void {
    panelRenderers.set(flag, component);
}

/**
 * The widget renderer registered for a key, or `undefined` when this build cannot draw it.
 *
 * `undefined` is the signal the shell falls back on. It is deliberately not an error and not a
 * blank: an unknown key is a real state the screen draws, so the seam reports it rather than
 * throwing inside a render.
 */
export function resolveWidget(key: string): Component | undefined {
    return widgetRenderers.get(key);
}

/**
 * The panel renderer registered for a flag, or `undefined` when this build cannot draw it. Separate
 * from `resolveWidget` so a widget key can never resolve a panel component (see the module header).
 */
export function resolvePanel(flag: string): Component | undefined {
    return panelRenderers.get(flag);
}

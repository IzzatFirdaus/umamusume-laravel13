import { createApp, defineComponent, h, nextTick, type DefineComponent } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { useTheme } from './composables/useTheme';
// Feature-detected polyfills for non-Baseline tooltip platform features
// (popover="hint" + interestfor). Each polyfill self-aborts when native
// support exists; never imported unconditionally.
import './polyfills';
// The HFW grid primitive, loaded after `app.css` through this entry so a peer
// build cannot leave a top-level CSS entry out of the manifest (that produced a
// Vite 500 for every page mid-run).
import '../css/layout-grid.css';

// Glob over nested page dirs, resolved lazily. The reason this is a glob and not a dynamic
// `import(\`./pages/${name}.vue\`)` is unchanged: a template import matches only a single path segment in
// Vite, so a nested page like `Preferences/Edit` resolved to nothing and mounted blank. `**` still fixes
// that, and dropping `eager: true` keeps the same map while handing Vite one dynamic import per page, so
// each screen becomes its own chunk instead of every screen riding on every other screen's components.
//
// The generic argument keeps the resolver type-safe in the lazy form too: the map's values become
// loaders for the same `{ default: DefineComponent }` module, so `resolve` still returns a typed
// component rather than `unknown`.
const pages = import.meta.glob<{ default: DefineComponent }>('./pages/**/*.vue');

/*
 * A page change inside the SPA leaves focus on the old control (or on `body`), so a keyboard or
 * screen-reader Trainer has no signal that the destination changed. The wizard already solves this
 * by focusing its own `h1` on mount (`SetupLayout.vue:128-169`); this is the same policy for the
 * pages that share the persistent shell, where an `onMounted` hook never fires again.
 *
 * Two guards keep it from stealing focus from work already in progress:
 *  - only a change of Inertia component counts. A filter, sort or search that re-renders the same
 *    page must leave the Trainer's input focused.
 *  - a validation response carries `errors`; those pages focus their own `role="alert"` and that
 *    correction must win.
 */
function registerNavigationFocus(): void {
    let previousComponent: string | null = null;

    router.on('navigate', (event) => {
        const { page } = event.detail;
        const changed = previousComponent !== null && previousComponent !== page.component;
        previousComponent = page.component;

        if (!changed) {
            return;
        }

        const errors = page.props.errors as Record<string, unknown> | undefined;
        if (errors !== undefined && Object.keys(errors).length > 0) {
            return;
        }

        void nextTick(() => {
            const heading = document.querySelector<HTMLElement>('main h1, h1');
            if (heading === null) {
                return;
            }
            heading.setAttribute('tabindex', '-1');
            heading.focus({ preventScroll: true });
        });
    });
}

createInertiaApp({
    // Inertia's bundled NProgress draws `role="bar"`, an invalid ARIA role that fails a page-wide
    // axe scan whenever a visit is in flight (KI-63). The default is off; the replacement is the
    // native `progress` indicator the second app below mounts.
    progress: false,
    resolve: async (name) => (await pages[`./pages/${name}.vue`]()).default,
    setup({ el, App, props, plugin }) {
        // The theme sync lives here rather than in a layout because not every page renders one, and
        // the Blade shell that carries `data-theme` is never re-rendered by an Inertia visit.
        const Root = defineComponent({
            setup() {
                useTheme();

                return () => h(App, props);
            },
        });

        createApp({ render: () => h(Root) })
            .use(plugin)
            .mount(el);

        registerNavigationFocus();
    },
});

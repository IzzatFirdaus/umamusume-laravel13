import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

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

createInertiaApp({
    // Inertia's bundled NProgress draws `role="bar"`, an invalid ARIA role that fails a page-wide
    // axe scan whenever a visit is in flight (KI-63). The default is off; the replacement is the
    // native `progress` indicator the second app below mounts.
    progress: false,
    resolve: async (name) => (await pages[`./pages/${name}.vue`]()).default,
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});

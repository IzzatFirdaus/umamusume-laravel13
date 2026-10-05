import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

// Eager glob over nested page dirs. A dynamic `import(\`./pages/${name}.vue\`)` only matches a
// single path segment in Vite, so a nested page like `Preferences/Edit` resolved to nothing and
// mounted blank; `import.meta.glob` with `**` handles any depth. Eager keeps the resolver
// type-safe and, for a handful of local pages, the upfront cost is nil.
const pages = import.meta.glob<{ default: DefineComponent }>('./pages/**/*.vue', { eager: true });

createInertiaApp({
    resolve: (name) => pages[`./pages/${name}.vue`].default,
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});

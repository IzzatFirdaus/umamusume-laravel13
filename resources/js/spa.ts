import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

// Pages resolve lazily by name from resources/js/pages/. Vite analyses the template
// literal and code-splits one chunk per page.
createInertiaApp({
    resolve: (name) => import(`./pages/${name}.vue`),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            // app.ts serves the legacy Blade screens during the 2.0 rewrite (ADR-0020 §1);
            // spa.ts boots the Inertia + Vue shell. Both share app.css.
            input: ['resources/css/app.css', 'resources/js/app.ts', 'resources/js/spa.ts'],
            refresh: true,
        }),
        tailwindcss(),
        vue(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

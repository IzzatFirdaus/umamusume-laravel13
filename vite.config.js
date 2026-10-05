import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            // spa.ts boots the Inertia + Vue shell and is the only JavaScript entry (ADR-0020 §1).
            // The legacy Blade entry went with the Blade shell (B1).
            input: ['resources/css/app.css', 'resources/js/spa.ts'],
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

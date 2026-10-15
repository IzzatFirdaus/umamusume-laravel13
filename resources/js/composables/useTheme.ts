import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

/**
 * Syncs the stored theme onto the document element.
 *
 * The theme reaches the first paint server-side (`AppServiceProvider` composes `$theme` and
 * `app.blade.php` prints `data-theme`), which is what G-20 asks for. The Blade shell is not
 * re-rendered by an Inertia visit, so saving a new theme on `/preferences` changed the stored row and
 * left `<html>` carrying the old attribute: the palette never flipped until a full reload. This
 * mirrors the shared prop onto the same attribute the shell writes, on mount and on every visit.
 *
 * A null theme is "follow the OS", and the stylesheet's dark ramp is gated on
 * `html[data-theme='dark']` rather than on a media query, so null has to resolve through
 * `prefers-color-scheme` here exactly as the shell's first-paint script does.
 */
export function useTheme(): void {
    const page = usePage();

    watch(
        () => page.props?.app?.theme ?? null,
        (theme) => {
            const resolved = theme ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : null);

            if (resolved === null) {
                document.documentElement.removeAttribute('data-theme');

                return;
            }

            document.documentElement.dataset.theme = resolved;
        },
        { immediate: true },
    );
}

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const appVersion = computed(() => page.props.app?.version ?? null);
const flashStatus = computed(() => page.props.flash?.status ?? null);

// "Am I on this destination?" answered as a prefix, not as equality, because a section's own
// sub-screens carry it in the URL: `/legacy/12` and `/legacy/compare` are both the Legacy Lab.
// `page.url` also carries the query string on a filtered list, so the comparison strips it —
// otherwise `aria-current="page"` silently falls off the moment a filter is applied. Exact
// equality had this working only on the bare index URLs, which is why it is stated here rather
// than left to look correct on the screen it was written for.
function isCurrent(to: string): boolean {
    const [path] = page.url.split('?');

    return path === to || path.startsWith(`${to}/`);
}

// The 2.0 navigation (docs/proposals/design-2.0.md §28). `to: null` = not built yet:
// the Veteran library arrives in the D16 slice, so it renders as a named absence rather
// than a dead link. `spa` marks the Inertia routes that client-navigate; the rest
// full-reload to the existing Blade screens during the rewrite.
//
// Legacy Lab became a link when slice D5 landed, which took the list from eight live
// destinations plus one absence to eight plus one — the count is unchanged, so
// Miller's Law (plan §13) holds without dropping anything. The Veteran library below
// is the remaining named absence and the one a future slice has to replace rather than
// add beside.
const items = [
    { label: 'Dashboard', to: '/', spa: true },
    { label: 'New Career', to: '/training-runs/create', spa: true },
    { label: 'Legacy Lab', to: '/legacy', spa: true },
    { label: 'Support Cards', to: '/support-cards', spa: true },
    { label: 'Skills', to: '/skills', spa: true },
    { label: 'Review', to: '/review', spa: true },
    { label: 'Veterans', to: null, spa: false },
    { label: 'Database', to: '/umamusume', spa: true },
    { label: 'Settings', to: '/preferences', spa: true },
];

const linkClass =
    'flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';
const activeClass = 'bg-raised text-ink-strong';
const disabledClass =
    'flex min-h-11 cursor-not-allowed items-center rounded-md px-3 text-sm font-medium text-ink-muted';
const mobileClass =
    'flex min-h-11 shrink-0 items-center whitespace-nowrap px-3 text-xs font-medium text-ink';
const mobileDisabledClass =
    'flex min-h-11 shrink-0 cursor-not-allowed items-center whitespace-nowrap px-3 text-xs font-medium text-ink-muted';
</script>

<template>
    <div class="flex min-h-screen bg-page text-ink">
        <a
            href="#main"
            class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:border-2 focus:border-rule focus:bg-raised focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-ink-strong"
        >
            Skip to content
        </a>

        <aside class="hidden w-64 shrink-0 flex-col border-r border-rule bg-panel md:flex">
            <div class="border-b border-rule px-4 py-4">
                <span class="text-sm font-bold tracking-wide text-ink-strong">TRAINER DESK</span>
            </div>
            <nav aria-label="Primary" class="flex-1 p-2">
                <ul class="space-y-1">
                    <li v-for="item in items" :key="item.label">
                        <Link
                            v-if="item.spa"
                            :href="item.to as string"
                            :class="[linkClass, isCurrent(item.to as string) ? activeClass : '']"
                            :aria-current="isCurrent(item.to as string) ? 'page' : undefined"
                        >
                            {{ item.label }}
                        </Link>
                        <a v-else-if="item.to" :href="item.to" :class="linkClass">
                            {{ item.label }}
                        </a>
                        <span v-else :class="disabledClass" title="Coming with Trainer Desk 2.0">
                            {{ item.label }}
                        </span>
                    </li>
                </ul>
            </nav>
            <div class="border-t border-rule px-4 py-3 text-xs text-ink-muted">
                Local data · Trainer Desk
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header
                role="banner"
                class="flex items-center justify-between gap-4 border-b border-rule bg-panel px-6 py-3"
            >
                <h1 class="text-base font-semibold text-ink-strong">
                    <slot name="title" />
                </h1>
                <span
                    v-if="appVersion"
                    class="font-mono text-xs tabular-nums text-ink-muted"
                    title="Application version"
                >
                    v{{ appVersion }}
                </span>
                <span v-else class="text-xs text-ink-muted" title="Application version not recorded">
                    N/A
                </span>
            </header>
            <main id="main" class="flex-1 px-6 py-6 pb-24 md:pb-6">
                <p
                    v-if="flashStatus"
                    class="mb-4 rounded border border-green-line bg-green-tint px-3 py-2 text-sm text-ink"
                >
                    {{ flashStatus }}
                </p>
                <slot />
            </main>
        </div>

        <!-- Mobile navigation (docs/proposals/design-2.0.md §41). The sidebar is desktop-only;
             below md the same destinations render as a scrollable bottom bar. -->
        <nav
            aria-label="Primary"
            class="fixed inset-x-0 bottom-0 z-40 flex overflow-x-auto border-t border-rule bg-panel md:hidden"
        >
            <template v-for="item in items" :key="item.label">
                <Link
                    v-if="item.spa"
                    :href="item.to as string"
                    :class="[mobileClass, isCurrent(item.to as string) ? activeClass : '']"
                    :aria-current="isCurrent(item.to as string) ? 'page' : undefined"
                >
                    {{ item.label }}
                </Link>
                <a v-else-if="item.to" :href="item.to" :class="mobileClass">
                    {{ item.label }}
                </a>
                <span v-else :class="mobileDisabledClass" title="Coming with Trainer Desk 2.0">
                    {{ item.label }}
                </span>
            </template>
        </nav>
    </div>
</template>

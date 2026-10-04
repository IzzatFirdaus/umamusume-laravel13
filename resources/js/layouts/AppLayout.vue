<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const active = computed(() => page.url);

// The 2.0 navigation (docs/proposals/design-2.0.md §28). `to: null` = not built yet:
// Legacy Lab and the Veteran library arrive in later ADR-0020 slices, so they render as
// a named absence rather than a dead link. `spa` marks the one Inertia route that
// client-navigates; the rest full-reload to the existing Blade screens during the rewrite.
const items = [
    { label: 'Dashboard', to: '/dashboard', spa: true },
    { label: 'New Career', to: '/training-runs/create', spa: false },
    { label: 'Legacy Lab', to: null, spa: false },
    { label: 'Support Decks', to: '/support-cards', spa: false },
    { label: 'Veterans', to: null, spa: false },
    { label: 'Database', to: '/umamusume', spa: false },
    { label: 'Settings', to: '/preferences', spa: false },
];

const linkClass =
    'flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';
const activeClass = 'bg-raised text-ink-strong';
const disabledClass =
    'flex min-h-11 cursor-not-allowed items-center rounded-md px-3 text-sm font-medium text-ink-muted';
</script>

<template>
    <div class="flex min-h-screen bg-page text-ink">
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
                            :class="[linkClass, active === item.to ? activeClass : '']"
                            :aria-current="active === item.to ? 'page' : undefined"
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
                class="flex items-center justify-between border-b border-rule bg-panel px-6 py-3"
            >
                <h1 class="text-base font-semibold text-ink-strong">
                    <slot name="title" />
                </h1>
            </header>
            <main id="main" class="flex-1 px-6 py-6">
                <slot />
            </main>
        </div>
    </div>
</template>

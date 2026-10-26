<script setup lang="ts">
/*
 * The HFW `.subnav` strip (database.html): the hub plus the nine reference-data areas, on every
 * Database screen. It is the area switcher the hub's tiles and the rail cannot provide at the
 * reading position, so a Trainer moving between areas never has to go back through the hub.
 *
 * The current item is marked by `aria-current="page"` and a rule rather than by colour alone. The
 * URLs are the registered routes' own paths; this component is the one place the list lives.
 */
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const AREAS: ReadonlyArray<{ label: string; href: string }> = [
    { label: 'Trainees', href: '/umamusume' },
    { label: 'Support cards', href: '/support-cards' },
    { label: 'Skills', href: '/skills' },
    { label: 'Races', href: '/database/races' },
    { label: 'Scenarios', href: '/database/scenarios' },
    { label: 'Events', href: '/database/events' },
    { label: 'Shop items', href: '/database/shop-items' },
    { label: 'Sparks', href: '/database/sparks' },
    { label: 'Sources', href: '/database/sources' },
];

const page = usePage();
const path = computed<string>(() => page.url.split('?')[0]);
const isCurrent = (href: string): boolean => path.value === href || path.value.startsWith(`${href}/`);
</script>

<template>
    <nav aria-label="Reference data areas" class="flex flex-wrap items-center gap-x-1 gap-y-1 border-b border-rule pb-2 text-sm">
        <Link
            href="/database"
            class="inline-flex min-h-11 items-center px-2 font-semibold"
            :class="path === '/database' ? 'text-ink-strong underline' : 'text-ink hover:underline'"
            :aria-current="path === '/database' ? 'page' : undefined"
        >Database</Link>
        <span aria-hidden="true" class="px-1 text-ink-faint">|</span>
        <Link
            v-for="area in AREAS"
            :key="area.href"
            :href="area.href"
            class="inline-flex min-h-11 items-center px-2"
            :class="isCurrent(area.href) ? 'font-semibold text-ink-strong underline' : 'text-ink-muted hover:text-ink hover:underline'"
            :aria-current="isCurrent(area.href) ? 'page' : undefined"
        >{{ area.label }}</Link>
    </nav>
</template>

<script setup lang="ts">
/*
 * The HFW `.subnav` strip (database.html:69-73, catalog.html:66-70, support-cards.html:66-69,
 * skills.html:66-69 and the three detail pages): the hub plus the nine reference-data areas. It is
 * the area switcher the hub's tiles and the global rail cannot provide at the reading position, so a
 * Trainer moving between areas never has to go back through the hub.
 *
 * It mounts on every Database page and every Library page that the HFW draws it on. The current item
 * is marked by weight, fill and `aria-current="page"` rather than colour alone. The hub carries
 * `aria-current` only: `.subnav-item.is-current` swaps the text to `ink-strong`, which would fight the
 * filled pill's `on-brand-chrome` if the hub took the item state as well.
 *
 * Three areas have two live URLs (`/umamusume` and `/database/trainees`, and the same for support
 * cards and skills). The aliases keep the right item current on both.
 */
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const AREAS: ReadonlyArray<{ label: string; href: string; aliases?: string[] }> = [
    { label: 'Trainees', href: '/umamusume', aliases: ['/database/trainees'] },
    { label: 'Support cards', href: '/support-cards', aliases: ['/database/supports'] },
    { label: 'Skills', href: '/skills', aliases: ['/database/skills'] },
    { label: 'Races', href: '/database/races' },
    { label: 'Scenarios', href: '/database/scenarios' },
    { label: 'Events', href: '/database/events' },
    { label: 'Shop items', href: '/database/shop-items' },
    { label: 'Sparks', href: '/database/sparks' },
    { label: 'Sources', href: '/database/sources' },
];

const page = usePage();
const path = computed<string>(() => page.url.split('?')[0]);
const onHub = computed<boolean>(() => path.value === '/database');

const isCurrent = (area: { href: string; aliases?: string[] }): boolean =>
    [area.href, ...(area.aliases ?? [])].some(
        (href) => path.value === href || path.value.startsWith(`${href}/`),
    );
</script>

<template>
    <nav aria-label="Reference data areas" class="subnav">
        <Link
            href="/database"
            class="subnav-hub"
            :aria-current="onHub ? 'page' : undefined"
        >Database</Link>
        <span class="subnav-sep" aria-hidden="true" />
        <Link
            v-for="area in AREAS"
            :key="area.href"
            :href="area.href"
            class="subnav-item"
            :class="isCurrent(area) ? 'is-current' : ''"
            :aria-current="isCurrent(area) ? 'page' : undefined"
        >{{ area.label }}</Link>
    </nav>
</template>

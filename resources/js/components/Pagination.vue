<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

/*
 * The paginator bar, one owner for the lists that page through a server-side paginator's `links`.
 * Byte-identical copies of this block shipped in four list components; a pager is one control with one
 * focus contract - 44px targets, `aria-current="page"` on the live page - so it has one home here.
 *
 * The pages are Inertia visits (`Link`), and paging carries `preserve-scroll`: the Trainer asked for
 * page two of a list they are reading, not a jump to the top of it. `link.label` arrives from Laravel's
 * own paginator, which writes its ellipsis and arrows as HTML entities, so the label renders as markup
 * rather than escaped text. Nothing user-typed reaches it, and the query string the links carry is the
 * controller's `withQueryString()`, not this component's.
 */
interface PagerLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{ links: PagerLink[] }>();
</script>

<template>
    <nav v-if="links.length > 3" aria-label="Pagination" class="mt-4 flex flex-wrap gap-2 text-sm">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                :aria-current="link.active ? 'page' : undefined"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-ink hover:bg-raised"
                :class="link.active ? 'bg-raised font-bold text-ink-strong' : ''"
            >
                <span v-html="link.label" />
            </Link>
            <span v-else class="inline-flex min-h-11 items-center px-3 text-ink-muted" v-html="link.label" />
        </template>
    </nav>
</template>

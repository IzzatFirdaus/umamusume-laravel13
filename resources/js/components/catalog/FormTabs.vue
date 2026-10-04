<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

// The multi-form control. A trainee with three costumes is one page with a choice, not a vertical
// stack of near-identical screens.
//
// Plain links carrying `aria-current`, not a CSS-only radio group. The Blade original switched
// panels with dynamically-named Tailwind peer classes (`peer/t0-checked:block`) that the scanner
// cannot generate from a template, so the panels never left `hidden` in the shipped build; and a
// radio group in a GET form needed a submit button to become addressable. Links are native
// keyboard targets, carry the address, survive a reload, and need no script or hidden content.
const props = defineProps<{
    cards: { id: number; title: string }[];
    activeCardId: number | null;
    slug: string;
    showUnconfirmed: boolean;
}>();

function href(id: number): string {
    const params = new URLSearchParams({ form: String(id) });

    if (props.showUnconfirmed) {
        params.set('show_unconfirmed', '1');
    }

    return `/umamusume/${props.slug}?${params.toString()}`;
}
</script>

<template>
    <div class="mt-8">
        <nav aria-label="Costume forms" class="flex flex-wrap gap-2">
            <Link
                v-for="card in cards"
                :key="card.id"
                :href="href(card.id)"
                preserve-scroll
                :aria-current="card.id === activeCardId ? 'page' : undefined"
                class="inline-flex min-h-11 items-center rounded-full border px-3 text-sm"
                :class="
                    card.id === activeCardId
                        ? 'border-pick-line bg-sunken font-semibold text-ink-strong'
                        : 'border-rule bg-panel font-normal text-ink hover:border-green-line'
                "
            >
                {{ card.title }}
            </Link>
        </nav>

        <div class="mt-4">
            <slot />
        </div>
    </div>
</template>

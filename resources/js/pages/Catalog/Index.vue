<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import RarityChip from '../../components/RarityChip.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Card {
    id: number;
    title: string;
    rarity_label: string;
    rarity_stars: string;
    is_debut_form: boolean;
    unconfirmed: boolean;
    global_release_date: string | null;
    global_release_date_display: string | null;
}

interface Trainee {
    id: number;
    slug: string;
    name: string;
    name_ja: string | null;
    release_status_label: string;
    max_rarity: { label: string; stars: string } | null;
    form_count: number;
    cards: Card[];
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

const props = defineProps<{
    umamusumes: Paginator<Trainee>;
    statuses: { value: string; label: string }[];
    currentStatus: string | null;
    search: string | null;
    showAllStatus: boolean;
    showUnconfirmed: boolean;
    allStatusesLabel: string;
}>();

const search = ref(props.search ?? '');
const status = ref(props.showAllStatus ? 'all' : (props.currentStatus ?? ''));
const showUnconfirmed = ref(props.showUnconfirmed);

function applyFilters(): void {
    router.get(
        '/umamusume',
        {
            search: search.value || undefined,
            status: status.value || undefined,
            show_unconfirmed: showUnconfirmed.value ? 1 : undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
}

const formCountLabel = (count: number): string => (count === 1 ? 'form' : 'forms');
</script>

<template>
    <AppLayout>
        <Head title="Database" />
        <template #title>Database</template>

        <h2 class="text-2xl font-semibold text-ink-strong">Umamusume catalog</h2>

        <form class="mt-4 flex flex-wrap items-end gap-3 text-sm" @submit.prevent="applyFilters">
            <label class="flex flex-col gap-1">
                <span class="text-ink-muted">Search</span>
                <input
                    v-model="search"
                    type="text"
                    name="search"
                    placeholder="Trainee or card name"
                    class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                >
            </label>
            <label class="flex flex-col gap-1">
                <span class="text-ink-muted">Release status</span>
                <select v-model="status" name="status" class="h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                    <option value="all">{{ allStatusesLabel }}</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
            </label>
            <label class="flex items-center gap-2 pb-1.5">
                <input v-model="showUnconfirmed" type="checkbox" name="show_unconfirmed" value="1" class="rounded border-rule">
                <span class="text-ink-muted">Show unconfirmed cards</span>
            </label>
            <button type="submit" class="enamel h-11 rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">
                Filter
            </button>
        </form>

        <p
            v-if="umamusumes.data.length === 0"
            class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            No Umamusume match. The catalog is filled by seed data or `php artisan uma:fetch`.
        </p>

        <template v-else>
            <ul class="mt-6 space-y-4">
                <li v-for="trainee in umamusumes.data" :key="trainee.id" class="rounded-md border border-rule bg-raised">
                    <h3 class="flex flex-wrap items-baseline justify-between gap-x-4 px-4 py-3">
                        <a :href="`/umamusume/${trainee.slug}`" class="font-semibold text-ink-strong hover:underline">
                            {{ trainee.name }}
                            <span v-if="trainee.name_ja" class="ml-2 text-sm font-normal text-ink-muted">
                                {{ trainee.name_ja }}
                            </span>
                        </a>
                        <span class="flex items-baseline gap-3 text-xs text-ink-muted">
                            <template v-if="trainee.max_rarity">
                                <RarityChip :label="trainee.max_rarity.label" :stars="trainee.max_rarity.stars" />
                                <span>{{ trainee.form_count }} {{ formCountLabel(trainee.form_count) }}</span>
                            </template>
                            <span v-else>no forms recorded</span>
                            <span>{{ trainee.release_status_label }}</span>
                        </span>
                    </h3>

                    <ul v-if="trainee.cards.length > 0" class="divide-y divide-rule border-t border-rule">
                        <li
                            v-for="card in trainee.cards"
                            :key="card.id"
                            class="flex flex-wrap items-baseline justify-between gap-x-4 px-4 py-2"
                        >
                            <h4 class="text-sm font-medium text-ink">{{ card.title }}</h4>
                            <span class="flex items-baseline gap-3 text-xs text-ink-muted">
                                <RarityChip :label="card.rarity_label" :stars="card.rarity_stars" />
                                <span v-if="card.is_debut_form">debut form</span>
                                <span v-if="card.unconfirmed" class="font-semibold text-risk">
                                    Not confirmed by two sources
                                </span>
                                <time v-if="card.global_release_date" :datetime="card.global_release_date">
                                    Released (Global) {{ card.global_release_date_display }}
                                </time>
                            </span>
                        </li>
                    </ul>
                </li>
            </ul>

            <nav v-if="umamusumes.links.length > 3" aria-label="Pagination" class="mt-4 flex flex-wrap gap-2 text-sm">
                <template v-for="(link, index) in umamusumes.links" :key="index">
                    <a
                        v-if="link.url"
                        :href="link.url"
                        :aria-current="link.active ? 'page' : undefined"
                        class="rounded-md border border-rule px-3 py-1 text-ink hover:bg-raised"
                        :class="link.active ? 'bg-raised font-bold text-ink-strong' : ''"
                        v-html="link.label"
                    />
                    <span v-else class="px-3 py-1 text-ink-muted" v-html="link.label" />
                </template>
            </nav>
        </template>
    </AppLayout>
</template>

<script setup lang="ts">
/*
 * The catalog list, rebuilt to `catalog.html` (plan Slice 8).
 *
 * Cards, not a table: a trainee is identified by her name and three aptitude letters, so the list is
 * a grid of cards and every further column waits behind the detail page (HFW annotation 1 and 2).
 * Each card names the form the portrait shows, that form's own rarity word, the number of forms the
 * catalogue holds for her, and the three strongest aptitude letters. The release status is a word,
 * never a machine token; a Japan-only row prints the label as muted text rather than as a badge,
 * because "not on Global" is an absence this tool states rather than a status it awards.
 *
 * The growth row stays on the card and states that the catalogue does not hold a growth figure. The
 * HFW prints a row of percentages because its catalogue carries them; this one does not, and a
 * `+0%` for every stat would be a fabricated figure (`+0%` is the print for a recorded zero, not for
 * a column this tool never fetched). Base values and the ★4/★5 totals are absent for the same
 * reason. `training_runs.growth_rate` is where a growth row lives, entered on the run that reads it.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import Pagination from '../../components/ui/Pagination.vue';
import RarityChip from '../../components/RarityChip.vue';
import BaseInput from '../../components/Form/BaseInput.vue';
import BaseSelect from '../../components/Form/BaseSelect.vue';
import BaseCheckbox from '../../components/Form/BaseCheckbox.vue';
import BaseButton from '../../components/Form/BaseButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface DefaultForm {
    title: string;
    rarity_label: string;
    rarity_stars: string;
    rarity_word: string;
    is_debut_form: boolean;
}

interface AptitudeLetter {
    axis: 'surface' | 'distance' | 'style';
    label: string;
    letter: string;
}

interface Trainee {
    id: number;
    slug: string;
    name: string;
    name_ja: string | null;
    /** The stored enum value. The page branches on this, never on the display word. */
    release_status: string;
    release_status_label: string;
    max_rarity: { label: string; stars: string } | null;
    artworkURL: string | null;
    /** The form the portrait shows, or null when the catalogue holds no form for her. */
    default_form: DefaultForm | null;
    /** The strongest letter per axis, or null when the source published no aptitudes. */
    aptitude_summary: AptitudeLetter[] | null;
    form_count: number;
}

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    umamusumes: Paginator<Trainee>;
    totalCount: number;
    askedFor: string;
    statuses: { value: string; label: string }[];
    currentStatus: string | null;
    search: string | null;
    showAllStatus: boolean;
    showUnconfirmed: boolean;
    allStatusesLabel: string;
}>();

const page = usePage();
const errors = computed(() => (page.props.errors as Record<string, string | undefined> | undefined) ?? {});

const search = ref(props.search ?? '');
const status = ref(props.showAllStatus ? 'all' : (props.currentStatus ?? ''));
const showUnconfirmed = ref(props.showUnconfirmed);
const loading = ref(false);

function applyFilters(): void {
    router.get(
        '/umamusume',
        {
            search: search.value || undefined,
            status: status.value || undefined,
            show_unconfirmed: showUnconfirmed.value ? 1 : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onStart: () => {
                loading.value = true;
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

function goToPage(page: number): void {
    router.get(
        '/umamusume',
        {
            search: search.value || undefined,
            status: status.value || undefined,
            show_unconfirmed: showUnconfirmed.value ? 1 : undefined,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

const formCountLabel = (count: number): string => (count === 1 ? 'form' : 'forms');

// The three letter pills are the list's own grammar: the label names the axis member the letter
// belongs to, so "Long B" reads as a distance rather than as a grade with no subject.
const aptitudePill = 'pill pill-outline';
const fieldLabel = 'text-xs font-semibold text-ink-muted';
const fieldControl = 'min-h-11 rounded-md border border-rule bg-panel px-3 text-sm text-ink';
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Database', href: '/database' }, { label: 'Trainees' }]">
        <Head title="Umamusume catalog" />
        <template #head>
            <PageHeader
                eyebrow="Catalog"
                title="Umamusume catalog"
                description="Every aptitude letter here is the catalog's. Each card names three letters, the strongest per axis; the detail page owns the full set."
            />
        </template>

        <DatabaseNav />

        <!-- One inline filter row, above the cards. A plain Inertia visit so the pending state and a
             rejected facet both stay readable on the field that caused them. -->
        <form
            class="flex flex-wrap items-end gap-3"
            :aria-busy="loading ? 'true' : null"
            @submit.prevent="applyFilters"
        >
            <label class="flex min-w-0 flex-1 basis-64 flex-col gap-1" :class="fieldLabel">
                Search
                <BaseInput
                    v-model="search"
                    type="text"
                    id="trainee-search"
                    name="search"
                    placeholder="Trainee or card name"
                    :error="errors.search"
                />
            </label>
            <label class="flex flex-col gap-1" :class="fieldLabel">
                Release status
                <BaseSelect
                    v-model="status"
                    id="trainee-status"
                    name="status"
                    :options="[{ value: 'all', label: allStatusesLabel }, ...props.statuses.map((option) => ({ value: option.value, label: option.label }))]"
                    placeholder="All"
                    :error="errors.status"
                />
            </label>
            <BaseCheckbox
                id="trainee-show-unconfirmed"
                v-model="showUnconfirmed"
                name="show_unconfirmed"
                value="1"
                label="Show unconfirmed cards"
                :error="errors.show_unconfirmed"
            />
            <BaseButton type="submit" variant="primary" size="md" :loading="loading">
                Filter
            </BaseButton>
            <a
                href="/umamusume"
                class="inline-flex min-h-11 items-center px-2 text-sm text-ink-muted hover:underline transition-colors duration-150 ease-out"
            >Clear</a>
        </form>

        <p v-if="loading" role="status" class="mt-4 text-sm text-ink-muted">Loading results…</p>

        <!-- Two absences, named apart: an empty catalogue is a fetch nobody has run, and an empty
             result is a question the data cannot answer. -->
        <div v-if="props.umamusumes.data.length === 0" class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6">
            <template v-if="props.totalCount === 0">
                <p class="text-sm font-semibold text-ink-strong">The trainee catalog holds no rows yet</p>
                <p class="mt-1 text-sm text-ink-muted">
                    Forms arrive with the character-card fetch. Until it runs, this screen has nothing to list.
                </p>
            </template>
            <template v-else>
                <p class="text-sm font-semibold text-ink-strong">No Umamusume match</p>
                <p class="mt-1 text-sm text-ink-muted">
                    Nothing matches {{ props.askedFor }}. If a trainee you expect is not in the catalog, that
                    is a gap in the fetched data rather than a mistake in the query.
                </p>
            </template>
        </div>

        <template v-else>
            <p class="mt-6 text-sm text-ink-muted">{{ props.umamusumes.total }} of {{ props.totalCount }} trainees</p>

            <ul id="catalog-results" class="mt-3 grid gap-3 g-3">
                <li v-for="trainee in props.umamusumes.data" :key="trainee.id">
                    <!-- The card is one link: the whole tile is the target, and the row holds no second
                         control, so nothing inside it competes for the click. The frame is decorative
                         because the name is printed beside it (`DESIGN.md` §4.7). -->
                    <Link
                        :href="`/umamusume/${trainee.slug}`"
                        class="card flex h-full flex-col gap-2 hover:border-ink-faint transition-colors duration-150 ease-out"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex min-w-0 items-center gap-2">
                                <ArtworkSlot :url="trainee.artworkURL" alt="" size="size-12" reserve />
                                <div class="min-w-0">
                                    <p class="truncate text-base font-semibold text-ink-strong">{{ trainee.name }}</p>
                                    <p v-if="trainee.name_ja" lang="ja" class="truncate text-xs text-ink-muted">{{ trainee.name_ja }}</p>
                                </div>
                            </div>
                            <!-- A word, not a token: the Global cases are pills, and the JP-only case is
                                 muted text because it is an absence from Global rather than a status
                                 this tool awards (HFW annotation 6). -->
                            <span
                                v-if="trainee.release_status === 'GlobalReleased'"
                                class="pill pill-positive shrink-0"
                            >{{ trainee.release_status_label }}</span>
                            <span
                                v-else-if="trainee.release_status === 'GlobalAnnounced'"
                                class="pill pill-caution shrink-0"
                            >{{ trainee.release_status_label }}</span>
                            <span v-else class="shrink-0 text-xs text-ink-muted">{{ trainee.release_status_label }}</span>
                        </div>

                        <!-- The form the card names, and its own rarity: two figures, because a card's
                             rarity and the trainee's best are not the same number (annotation 3). -->
                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1 text-xs">
                            <template v-if="trainee.default_form">
                                <b class="text-ink-strong">{{ trainee.default_form.title }}</b>
                                <span class="pill pill-neutral">
                                    <RarityChip
                                        :label="trainee.default_form.rarity_label"
                                        :stars="trainee.default_form.rarity_stars"
                                    />
                                    {{ trainee.default_form.rarity_word }}
                                </span>
                                <span class="text-ink-muted">{{ trainee.form_count }} {{ formCountLabel(trainee.form_count) }}</span>
                            </template>
                            <span v-else class="text-ink-muted">No costume form is recorded for her yet.</span>
                        </div>

                        <div v-if="trainee.aptitude_summary" class="flex flex-wrap gap-1.5">
                            <span
                                v-for="letter in trainee.aptitude_summary"
                                :key="letter.axis"
                                :class="aptitudePill"
                            >{{ letter.label }} {{ letter.letter }}</span>
                        </div>
                        <p v-else class="text-xs text-ink-muted">Aptitude not published for this trainee.</p>

                        <!-- The growth row keeps its place and states the absence. A stat this catalogue
                             never fetched is not a zero, so no percentage prints here. -->
                        <p class="text-xs text-ink-muted">
                            Growth <span class="text-ink">not stored</span> · the catalogue holds no growth row for any form
                        </p>
                    </Link>
                </li>
            </ul>

            <Pagination
                :page="props.umamusumes.current_page"
                :pages="props.umamusumes.last_page"
                :from="props.umamusumes.from ?? 0"
                :to="props.umamusumes.to ?? 0"
                :total="props.umamusumes.total"
                noun="trainees"
                @navigate="goToPage"
            />
        </template>

        <p class="mt-6 max-w-3xl text-xs text-ink-muted">
            A costume form's rarity is its own, not the trainee's best: a debut form can be an SR while a
            later form is an SSR. Base stat values, the ★4 and ★5 totals and growth rows are not stored in
            this catalogue; a run records the growth row its Trainer reads off the card.
        </p>
    </AppLayout>
</template>

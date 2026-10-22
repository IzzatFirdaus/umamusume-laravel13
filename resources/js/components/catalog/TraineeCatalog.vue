<script setup lang="ts">
/*
 * The Umamusume roster tree, shared by the catalog index (`/umamusume`) and the Database hub's
 * Trainees area (`/database/trainees`, SCREEN-023, plan §8 D17).
 *
 * One list body, two URLs. The page that mounts it owns the shell, the heading and the props, and
 * passes the address the filter form writes back to, so a Trainer filtering from the Database URL
 * stays on it rather than being carried to the URL this query was first written for.
 */
import ArtworkSlot from '../ArtworkSlot.vue';
import Pagination from '../ui/Pagination.vue';
import RarityChip from '../RarityChip.vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';
import SparkChip from '../SparkChip.vue';
import BaseInput from '../Form/BaseInput.vue';
import BaseSelect from '../Form/BaseSelect.vue';
import BaseCheckbox from '../Form/BaseCheckbox.vue';
import BaseButton from '../Form/BaseButton.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Card {
    id: number;
    title: string;
    rarity_label: string;
    rarity_stars: string;
    is_debut_form: boolean;
    unconfirmed: boolean;
    global_release_date: string | null;
    global_release_date_display: string | null;
    artworkURL: string | null;
}

interface Trainee {
    id: number;
    slug: string;
    name: string;
    name_ja: string | null;
    release_status_label: string;
    max_rarity: { label: string; stars: string } | null;
    artworkURL: string | null;
    form_count: number;
    cards: Card[];
    /** Provenance state for the trainee's data. */
    provenance?: 'confirmed' | 'calculated' | 'estimated' | 'unknown';
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
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
    /** Where the filter form and the pager write back to. Required: a caller that omits it would
     *  silently filter from the Database URL back to `/umamusume`. */
    filterPath: string;
}>();

const search = ref(props.search ?? '');
const status = ref(props.showAllStatus ? 'all' : (props.currentStatus ?? ''));
const showUnconfirmed = ref(props.showUnconfirmed);
const loading = ref(false);
const page = usePage();

/*
 * The roster filter's own validation errors, read the way the skills and support-card siblings read
 * theirs and rendered on the field that failed rather than as a bare line under the form.
 *
 * `CatalogSearchRequest` validates `status` against `ReleaseStatus`, and a rejected status came
 * back in the error bag and was dropped: this page read no `page.props.errors` at all, while the
 * two sibling catalogues read theirs. The surface is not a Trainer picking a bad value - the
 * dropdown offers only valid ones - but a URL that carries one (a bookmark, a tampered link, a
 * filter state cached from before the validator tightened), which reaches the server exactly like
 * a good one and has to be readable when it comes back.
 */
const errors = computed(() => (page.props.errors as Record<string, string | undefined> | undefined) ?? {});

function applyFilters(): void {
    router.get(
        props.filterPath,
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

const formCountLabel = (count: number): string => (count === 1 ? 'form' : 'forms');
</script>

<template>
    <h2 class="text-lg font-semibold text-ink-strong">Umamusume catalog</h2>

    <div class="mt-4 rounded-md border border-rule bg-raised p-4">
        <form
            class="flex flex-wrap items-end gap-3 text-sm"
            :aria-busy="loading ? 'true' : null"
            @submit.prevent="applyFilters"
        >
            <label class="flex flex-col gap-1">
                <span class="text-ink-muted">Search</span>
                <BaseInput
                    v-model="search"
                    type="text"
                    id="trainee-search"
                    name="search"
                    placeholder="Trainee or card name"
                    :error="errors.search"
                />
            </label>
            <label class="flex flex-col gap-1">
                <span class="text-ink-muted">Release status</span>
                <BaseSelect
                    v-model="status"
                    id="trainee-status"
                    name="status"
                    :options="[{ value: 'all', label: allStatusesLabel }, ...statuses.map((s) => ({ value: s.value, label: s.label }))]"
                    placeholder="All"
                    :error="errors.status"
                />
            </label>
            <!-- `BaseCheckbox` renders its own `<label>`; wrapping it in a second one is invalid HTML
                 and broke the implicit-label association, which axe reports as a critical unlabelled
                 control. The sibling `skills/SkillCatalog.vue` calls it the same way. -->
            <BaseCheckbox
                v-model="showUnconfirmed"
                id="trainee-show-unconfirmed"
                name="show_unconfirmed"
                value="1"
                label="Show unconfirmed cards"
                :error="errors.show_unconfirmed"
            />
            <BaseButton type="submit" variant="primary" size="md" :loading="loading">
                Filter
            </BaseButton>
        </form>
    </div>

    <p v-if="loading" role="status" class="mt-4 text-sm text-ink-muted">Loading results…</p>

    <!-- Two different absences, in the two shapes the skill and support siblings already use: an empty
         catalog is a mirror nobody has fetched, an empty result is a question the data cannot answer. -->
    <template v-if="umamusumes.data.length === 0">
        <p
            v-if="totalCount === 0"
            class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            The trainee catalog holds no rows yet. Run <code>php artisan uma:fetch</code> to fill it, or
            <code>php artisan uma:reparse gametora-character-cards</code> if a fetch says the document is
            unchanged.
        </p>
        <p
            v-else
            class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            No Umamusume match. Nothing matches {{ askedFor }}. If a trainee you expect is not in the
            catalog, that is a gap in the fetched data rather than a mistake in the query.
        </p>
    </template>

    <template v-else>
        <p class="mt-6 text-sm text-ink-muted">{{ umamusumes.total }} of {{ totalCount }} trainees</p>

        <ul class="mt-2 space-y-4">
            <li v-for="trainee in umamusumes.data" :key="trainee.id" class="rounded-md border border-rule bg-raised">
                <h3 class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-3">
                    <!-- Frame and name are one group, not two siblings of a `justify-between` row.
                         With three children that class distributed the leftover space and floated
                         the name to the row's centre: 89px from its own portrait, and at a
                         different x in every row (measured 411 to 447), which broke the Law of
                         Proximity and left no scanning column on a list meant to be scanned.
                         `reserve` holds the cell so the name starts at one x whether or not the
                         mirror happens to hold the file.

                         The header already prints her name beside the frame, so the image is
                         decorative and takes `alt=""` (DESIGN.md §4.7); a screen reader reads the
                         name once, from the text. No `href`: §45a makes the row's own name link
                         the destination, so a second link to the same page would be a duplicate
                         control. -->
                    <span class="flex items-center gap-3">
                        <ArtworkSlot :url="trainee.artworkURL" alt="" size="size-12" reserve />
                        <!-- The hit area is the link's own: the row is not a target (the artwork slot
                             carries no `href` and there is no row handler), so the name link is the only
                             way into the detail. `min-h-11` grows that target to the house 44px bar
                             (DESIGN.md §12, KI-29) without touching the type. This one is arc-class
                             rather than an AA failure: it measured 28px, above the 24px floor. -->
                        <Link :href="`/umamusume/${trainee.slug}`" class="inline-flex min-h-11 items-center text-lg font-semibold text-ink-strong hover:underline transition-colors duration-150 ease-out">
                            {{ trainee.name }}
                            <span v-if="trainee.name_ja" lang="ja" class="ml-2 text-sm font-normal text-ink-muted">
                                {{ trainee.name_ja }}
                            </span>
                        </Link>
                    </span>
                    <span class="flex items-baseline gap-3 text-xs text-ink-muted">
                        <template v-if="trainee.max_rarity">
                            <RarityChip :label="trainee.max_rarity.label" :stars="trainee.max_rarity.stars" />
                            <SparkChip :label="`${trainee.form_count} ${formCountLabel(trainee.form_count)}`" variant="consumable" size="sm" />
                        </template>
                        <span v-else>no forms recorded</span>
                        <ProvenanceBadge
                            v-if="trainee.provenance"
                            :state="trainee.provenance"
                        />
                        <span>{{ trainee.release_status_label }}</span>
                    </span>
                </h3>

                <details v-if="trainee.cards.length > 0" class="border-t border-rule">
                    <!-- The trainee's own header stays visible; only the costume-form rows fold. A
                         roster of 67 trainees each printing every form made the list unscannable, and
                         the count is already on the header, so the summary repeats it rather than
                         naming her a second time. -->
                    <summary class="min-h-11 cursor-pointer px-4 py-2 text-sm text-ink-muted">
                        {{ trainee.cards.length }} {{ formCountLabel(trainee.cards.length) }}
                    </summary>

                    <ul class="divide-y divide-rule">
                        <li
                            v-for="card in trainee.cards"
                            :key="card.id"
                            class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-2"
                        >
                            <!-- The form's own portrait, keyed on its card id, at the `size-10` geometry
                                 §45a records for a costume-form row. Grouped with the title for the same
                                 reason the header is: three siblings in a `justify-between` row centre
                                 the middle one. A `div` rather than a `span`, because this group holds an
                                 `h4`, and flow content is not allowed inside a `span`. Decorative like
                                 the header frame: this row prints the form title beside it, and the file
                                 is a trainee portrait, so the name lives in the header, not here. -->
                            <div class="flex items-center gap-3">
                                <ArtworkSlot :url="card.artworkURL" alt="" size="size-10" reserve />
                                <h4 class="text-lg font-semibold text-ink">{{ card.title }}</h4>
                            </div>
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
                </details>
            </li>
        </ul>

        <Pagination :links="umamusumes.links" />
    </template>
</template>

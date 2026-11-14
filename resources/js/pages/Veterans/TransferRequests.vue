<script setup lang="ts">
/*
 * The Transfer Requests board (`veterans.transfers`).
 *
 * One banked career offered for transfer, one row. The lifecycle is the model's own
 * (offered -> completed | withdrawn): completing permanently removes the Veteran and
 * records the reward for audit; withdrawing returns the Veteran to the library. No
 * wallet exists, so a reward is recorded, never paid out on this screen.
 *
 * Every url arrives server-side (`transferUrls`, `veteransIndexUrl`): the shell carries
 * no Ziggy, so `route()` is not callable from page code. Errors arrive through Inertia's
 * shared `errors` prop; confirmations ride the session's `status` flash, which AppLayout's
 * own banner prints — this page renders no flash of its own.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface TransferRequest {
    id: number;
    status: 'offered' | 'completed' | 'withdrawn';
    reward: { star_pieces?: number; monies?: number; support_points?: number } | null;
    created_at: string;
    completed_at: string | null;
    withdrawn_at: string | null;
    veteran: {
        id: number;
        training_run: {
            umamusume: {
                name: string;
                name_ja: string | null;
            } | null;
        } | null;
    } | null;
}

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    offered: Paginator<TransferRequest>;
    completed: Paginator<TransferRequest>;
    withdrawn: Paginator<TransferRequest>;
    /** Server-composed action urls, keyed by request id. */
    transferUrls: Record<string, { complete: string; withdraw: string }>;
    /** Server-composed door back to the library. */
    veteransIndexUrl: string;
}

const props = defineProps<Props>();

const activeTab = ref<'offered' | 'completed' | 'withdrawn'>('offered');
const loading = ref(false);
const actionError = ref<string | null>(null);

const page = usePage();
const errors = computed(() => (page.props.errors ?? {}) as Record<string, string>);

const tabs = [
    { key: 'offered' as const, label: 'Offered', count: computed(() => props.offered.total) },
    { key: 'completed' as const, label: 'Completed', count: computed(() => props.completed.total) },
    { key: 'withdrawn' as const, label: 'Withdrawn', count: computed(() => props.withdrawn.total) },
];

const currentList = computed(() => props[activeTab.value]);

const veteranDisplay = (transfer: TransferRequest): string => {
    const name = transfer.veteran?.training_run?.umamusume?.name ?? 'Removed Veteran';
    const ja = transfer.veteran?.training_run?.umamusume?.name_ja;

    return ja === null || ja === undefined || ja === name ? name : `${name} (${ja})`;
};

/** Complete removes the Veteran permanently, so the confirm names the cost before it lands. */
const handleComplete = (transfer: TransferRequest): void => {
    if (!confirm(
        `Complete transfer for "${veteranDisplay(transfer)}"? `
        + 'This permanently removes the Veteran from your library.',
    )) {
        return;
    }

    loading.value = true;
    actionError.value = null;
    router.put(
        props.transferUrls[String(transfer.id)]?.complete ?? '',
        { reward: transfer.reward ?? {} },
        {
            preserveScroll: true,
            onFinish: () => {
                loading.value = false;
            },
        },
    );
};

const handleWithdraw = (transfer: TransferRequest): void => {
    if (!confirm(
        `Withdraw the transfer offer for "${veteranDisplay(transfer)}"? `
        + 'The Veteran will remain in your library.',
    )) {
        return;
    }

    loading.value = true;
    actionError.value = null;
    router.delete(props.transferUrls[String(transfer.id)]?.withdraw ?? '', {
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
        },
    });
};

/** A page move is a visit, not a client-side slice: the tab rides the query string. */
const goToPage = (pageNum: number): void => {
    router.get(props.veteransIndexUrl, { tab: activeTab.value, page: pageNum }, { preserveScroll: true });
};

/** The recorded reward, printed as amounts, or the named absence with its reason. */
const rewardText = (transfer: TransferRequest): { text: string; absent: boolean } => {
    const reward = transfer.reward;

    if (reward === null || typeof reward !== 'object') {
        return { text: 'N/A', absent: true };
    }

    const parts: string[] = [];
    if (typeof reward.star_pieces === 'number' && reward.star_pieces > 0) {
        parts.push(`${reward.star_pieces} Star Piece${reward.star_pieces === 1 ? '' : 's'}`);
    }
    if (typeof reward.monies === 'number' && reward.monies > 0) {
        parts.push(`${reward.monies.toLocaleString('en-US')} Monies`);
    }
    if (typeof reward.support_points === 'number' && reward.support_points > 0) {
        parts.push(`${reward.support_points} Support Point${reward.support_points === 1 ? '' : 's'}`);
    }

    return parts.length === 0 ? { text: 'N/A', absent: true } : { text: parts.join(', '), absent: false };
};

const formatDate = (value: string | null): { text: string; absent: boolean } => {
    if (value === null) {
        return { text: 'N/A', absent: true };
    }

    return { text: new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }), absent: false };
};
</script>

<template>
    <AppLayout>
        <Head title="Transfer Requests" />

        <header class="flex flex-wrap items-baseline justify-between gap-2">
            <div class="min-w-0">
                <h1 class="text-lg font-semibold text-ink-strong">Transfer Requests</h1>
                <p class="mt-1 text-sm text-ink-muted">
                    Offers to transfer a banked career. Completing one removes the Veteran permanently;
                    the reward is recorded for the record, and no wallet pays it out in this build.
                </p>
            </div>
            <a
                :href="props.veteransIndexUrl"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
            >Back to Veterans</a>
        </header>

        <p v-if="errors.reward" role="alert" class="mt-2 text-sm text-risk">Reward: {{ errors.reward }}</p>
        <p v-if="actionError" role="alert" class="mt-2 text-sm text-risk">{{ actionError }}</p>

        <!-- The three lifecycle states as one tab row; each carries its own live count. -->
        <div class="mt-4 flex flex-wrap items-center gap-2" role="tablist" aria-label="Transfer request states">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                role="tab"
                :aria-selected="activeTab === tab.key"
                class="inline-flex min-h-11 items-center gap-1.5 rounded-md border px-3 text-sm font-medium transition-colors duration-150 ease-out"
                :class="activeTab === tab.key
                    ? 'border-ink-strong bg-raised text-ink-strong'
                    : 'border-rule text-ink-muted hover:bg-raised hover:text-ink'"
                @click="activeTab = tab.key"
            >
                {{ tab.label }}
                <span class="rounded-pill bg-sunken px-2 py-0.5 text-xs text-ink">{{ tab.count.value }}</span>
            </button>
        </div>

        <div class="mt-4">
            <p
                v-if="currentList.data.length === 0"
                class="rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
                data-testid="no-transfers"
            >
                No {{ activeTab }} transfer requests. The list fills as offers are made from a Veteran's
                own page.
            </p>

            <ul v-else class="space-y-2" :data-tab="activeTab">
                <li
                    v-for="transfer in currentList.data"
                    :key="transfer.id"
                    class="rounded-md border border-rule bg-panel p-3"
                    :data-transfer-row="transfer.id"
                >
                    <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 min-w-0">
                        <p class="font-medium text-ink-strong">{{ veteranDisplay(transfer) }}</p>
                        <p class="text-sm text-ink-muted">
                            <template v-if="activeTab === 'offered'">
                                Offered {{ formatDate(transfer.created_at).text }}
                            </template>
                            <template v-else-if="activeTab === 'completed'">
                                Completed
                                <span
                                    :title="formatDate(transfer.completed_at).absent
                                        ? 'This record states no completion date.'
                                        : undefined"
                                >{{ formatDate(transfer.completed_at).text }}</span>
                            </template>
                            <template v-else>
                                Withdrawn
                                <span
                                    :title="formatDate(transfer.withdrawn_at).absent
                                        ? 'This record states no withdrawal date.'
                                        : undefined"
                                >{{ formatDate(transfer.withdrawn_at).text }}</span>
                            </template>
                        </p>
                    </div>
                    <p class="mt-1 text-sm text-ink-muted">
                        Reward:
                        <span
                            :title="rewardText(transfer).absent
                                ? 'No reward was recorded on this request.'
                                : undefined"
                        >{{ rewardText(transfer).text }}</span>
                    </p>

                    <div
                        v-if="activeTab === 'offered'"
                        class="mt-2 flex flex-wrap items-center gap-2"
                    >
                        <button
                            type="button"
                            class="inline-flex min-h-11 items-center rounded-md bg-primary-action px-3 text-sm font-semibold text-on-primary-action transition-colors duration-150 ease-out"
                            :disabled="loading"
                            @click="handleComplete(transfer)"
                        >Complete</button>
                        <button
                            type="button"
                            class="inline-flex min-h-11 items-center rounded-md border border-rule bg-sunken px-3 text-sm font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
                            :disabled="loading"
                            @click="handleWithdraw(transfer)"
                        >Withdraw</button>
                    </div>
                </li>
            </ul>
        </div>

        <!-- One in-card pager per tab; only the live tab's is reachable. -->
        <div
            v-if="currentList.last_page > 1"
            class="mt-3 flex flex-wrap items-center gap-2"
        >
            <button
                v-for="pageNum in currentList.last_page"
                :key="pageNum"
                type="button"
                class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md border text-sm transition-colors duration-150 ease-out"
                :class="pageNum === currentList.current_page
                    ? 'border-ink-strong bg-raised text-ink-strong'
                    : 'border-rule text-ink-muted hover:bg-raised'"
                :aria-current="pageNum === currentList.current_page ? 'page' : undefined"
                @click="goToPage(pageNum)"
            >{{ pageNum }}</button>
        </div>
    </AppLayout>
</template>

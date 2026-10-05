<script setup lang="ts">
import AppLayout from '../layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Props {
    counts: { trainees: number; skills: number; supportCards: number };
}

defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />
        <template #title>Dashboard</template>

        <div class="space-y-6">
            <p class="text-ink">Welcome back, Trainer.</p>

            <!-- Active career: empty state (design-2.0 §29). The career cockpit and its
                 resume flow arrive with the SPA rewrite; nothing is active to show yet. -->
            <section class="rounded-md border border-rule bg-panel p-5">
                <h2 class="text-xs font-bold tracking-wide text-ink-muted">ACTIVE CAREER</h2>
                <p class="mt-2 text-sm text-ink">No active career. Start a new training run.</p>
                <a
                    href="/training-runs/create"
                    class="mt-4 inline-flex min-h-11 items-center rounded-md bg-chrome px-4 text-sm font-bold text-on-chrome hover:opacity-90"
                >
                    New Career
                </a>
            </section>

            <!-- Data status: real catalogue counts, CONFIRMED database values (design-2.0 §31). -->
            <section class="rounded-md border border-rule bg-panel p-5">
                <h2 class="text-xs font-bold tracking-wide text-ink-muted">DATA STATUS</h2>
                <dl class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs text-ink-muted">Trainees</dt>
                        <dd class="font-mono text-2xl tabular-nums text-ink-strong">
                            {{ counts.trainees.toLocaleString() }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Skills</dt>
                        <dd class="font-mono text-2xl tabular-nums text-ink-strong">
                            {{ counts.skills.toLocaleString() }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Support cards</dt>
                        <dd class="font-mono text-2xl tabular-nums text-ink-strong">
                            {{ counts.supportCards.toLocaleString() }}
                        </dd>
                    </div>
                </dl>
            </section>

            <!-- Veterans: empty state. The record-only Veteran library is ADR-0020 §3,
                 a later slice; named here rather than left blank. -->
            <section class="rounded-md border border-rule bg-panel p-5">
                <h2 class="text-xs font-bold tracking-wide text-ink-muted">VETERANS</h2>
                <p class="mt-2 text-sm text-ink-muted">
                    No veterans yet. The Veteran library arrives with Trainer Desk 2.0.
                </p>
            </section>
        </div>
    </AppLayout>
</template>

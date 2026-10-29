<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

/*
 * The snapshot entry point. One action, one paragraph, one way back: a Trainer arriving here is
 * holding a client that is partway through a career, and the thing this screen must not do is send
 * them down a wizard whose first step assumes turn 1. The setup form is the next screen and does the
 * work; this one only says what the door is for.
 */
</script>

<template>
    <AppLayout>
        <Head title="Start from existing snapshot" />
        <template #title>Start from existing snapshot</template>

        <!-- The HFW's snapshot stepper: Entry is live, Setup and Review are the links forward. -->
        <nav aria-label="Snapshot steps" class="flex flex-wrap items-center gap-2 text-sm">
            <span aria-current="step" class="inline-flex min-h-11 items-center rounded-full border-2 border-pick-line bg-green-tint px-3 font-bold text-ink-strong">Entry</span>
            <a href="/career/snapshot/setup" class="inline-flex min-h-11 items-center rounded-full border border-rule px-3 font-medium text-ink hover:bg-raised">Setup</a>
            <a href="/career/snapshot/review" class="inline-flex min-h-11 items-center rounded-full border border-rule px-3 font-medium text-ink hover:bg-raised">Review</a>
        </nav>

        <section aria-labelledby="snapshot-entry-heading" class="card mt-3.5 max-w-3xl">
            <h2 id="snapshot-entry-heading" class="text-lg font-semibold text-ink-strong">
                Start from existing snapshot
            </h2>
            <p class="mt-2 text-sm text-ink">
                Record a career that is already underway, exactly as far along as your client shows it.
                Nothing pretends it is turn 1: the position you enter becomes the run's own position,
                and every surface that counts from there counts from that point.
            </p>
            <p class="mt-2 text-sm text-ink-muted">
                Enter what you can read and leave what you cannot. A figure you have not read is
                recorded as absent rather than guessed at, so the tool never states a number you did
                not give it.
            </p>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <a
                    href="/career/snapshot/setup"
                    class="inline-flex min-h-11 items-center rounded-md bg-primary-action px-4 text-sm font-bold text-on-primary-action"
                >
                    Enter the snapshot
                </a>
                <a
                    href="/"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
                >
                    Back to the dashboard
                </a>
            </div>
        </section>
    </AppLayout>
</template>

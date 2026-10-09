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

        <section aria-labelledby="snapshot-entry-heading" class="max-w-2xl rounded-md border border-rule bg-panel p-6">
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
                    class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome"
                >
                    Enter the snapshot
                </a>
                <a
                    href="/"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised"
                >
                    Back to the dashboard
                </a>
            </div>
        </section>
    </AppLayout>
</template>

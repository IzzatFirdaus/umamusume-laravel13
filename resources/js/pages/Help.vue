<script setup lang="ts">
import AppLayout from '../layouts/AppLayout.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import { Head, Link } from '@inertiajs/vue3';
import { LinkButton } from '../components/Form';
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Help' }]">
        <Head title="Help" />
        <template #head>
            <PageHeader
                eyebrow="Help"
                title="How a career works"
                description="The turn loop this tool tracks, and where each screen answers a question."
            />
        </template>

        <div class="grid items-start gap-4 xl:grid-cols-2">
            <section class="rounded-md border border-rule bg-panel p-4" aria-labelledby="help-loop">
                <h2 id="help-loop" class="text-lg font-semibold text-ink-strong">The turn loop</h2>
                <ul class="mt-2 list-disc pl-5 text-sm text-ink">
                    <li>Start a career from the Dashboard: choose a scenario, a trainee and your targets.</li>
                    <li>Each turn, the Cockpit names one decision: training, a race, an event, a rest.</li>
                    <li>Log the result on the run page; the tool records Energy, stats and fans as you enter them.</li>
                    <li>A career is 72 turns. The finale block sits outside those 72 and ends the run.</li>
                    <li>A finished career becomes a Veteran on its Result screen and can be inherited by the next run.</li>
                </ul>
            </section>

            <section class="rounded-md border border-rule bg-panel p-4" aria-labelledby="help-undo">
                <h2 id="help-undo" class="text-lg font-semibold text-ink-strong">Mistakes and undo</h2>
                <p class="mt-2 text-sm text-ink">
                    There is no undo. Every write names itself before it happens, and the screens ask
                    before anything destructive. If a value is wrong, correct it on the screen that owns
                    it; a logged turn can be edited on the run page.
                </p>
                <p class="mt-2 text-sm text-ink-muted">
                    The help content is a first pass; the database hub answers provenance questions and
                    the scenario guides under the Database area answer mechanics questions.
                </p>
            </section>
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
            <LinkButton href="/" variant="primary">Back to the Dashboard</LinkButton>
            <Link href="/database/sources" class="inline-flex min-h-11 items-center text-sm font-medium text-ink underline">Source ledger</Link>
        </div>
    </AppLayout>
</template>
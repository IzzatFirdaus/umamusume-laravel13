<script setup lang="ts">
/*
 * Help (HFW help.html, plan Slice 11.2). Four questions a Trainer arrives with, answered in the
 * wireframe's order: how a turn goes, what the keys do, what the tags mean, and where a number came
 * from. The wireframe's `.keybox` annotation block and its "Static wireframe" chip are document
 * apparatus and do not ship.
 *
 * Two rules the page holds itself to. Nothing links to a route that does not exist: the decision,
 * timeline and cockpit screens are all scoped to one career, so the turn loop names them as text and
 * links only the screens a Trainer can open from here. And nothing claims a control the build does
 * not have: the keyboard table lists what is wired, and Undo is described as the gap it is.
 */
import AppLayout from '../layouts/AppLayout.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import { LinkButton } from '../components/Form';
import { Head, Link } from '@inertiajs/vue3';

// The trust vocabulary, in the order a Trainer meets it. `confirmed` is deliberately absent: an
// unmarked figure IS the confirmed state, which is why the card says so above the table.
const tags: { mark: string; name: string; meaning: string }[] = [
    { mark: '∑', name: 'Calculated', meaning: 'The tool derived it from values you entered. The rule is stated beside it.' },
    { mark: '~', name: 'Estimated', meaning: 'An approximate figure, or one resting on a single source.' },
    { mark: '?', name: 'Unknown', meaning: 'Not recorded, or not published. It is not a zero.' },
];

const rules: { name: string; fact: string }[] = [
    { name: 'Energy', fact: '50 is the sourced advisory line. Below it the failure curve changes character rather than degrading evenly.' },
    { name: 'Mood', fact: 'Five tiers. Great +20%, Good +10%, Normal 0, Bad −10%, Awful −20% on training yield.' },
    { name: 'Sparks', fact: 'Blue is the stat spark, Pink the aptitude spark, Green the unique-skill hint and White the skill hint. The cap raise is a separate mechanic.' },
    { name: 'Bond', fact: '0 to 100. A card turns orange at 80, which is where Friendship Training becomes available.' },
    { name: 'Deck', fact: 'Six slots. Five from your own cards and one guest rental. Two copies of one card cannot be equipped together.' },
    { name: 'Career length', fact: 'Seventy-two numbered turns. The finale sits outside that count and ends the run.' },
];

const keys: { key: string; does: string }[] = [
    { key: 'Tab / Shift+Tab', does: 'Move between controls. The focus ring is always visible.' },
    { key: 'Enter / Space', does: 'Activate the focused control, including a card that is itself the button.' },
    { key: 'Esc', does: 'Close the mobile More disclosure and return focus to it.' },
    { key: 'Skip to content', does: 'The first control on every screen; it lands in the page body, past the navigation.' },
];
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Help' }]">
        <Head title="Help and documentation" />
        <template #head>
            <PageHeader
                eyebrow="Help and documentation"
                title="How this works"
                description="The screen is meant to be usable without this page. This page exists for the two cases where it is not: a rule that is easier to look up than to infer, and a state that needs diagnosing."
            >
                <template #actions>
                    <LinkButton href="/" variant="secondary">Back to the dashboard</LinkButton>
                </template>
            </PageHeader>
        </template>

        <div class="grid items-start gap-4 @7xl:grid-cols-[minmax(0,2.1fr)_minmax(0,1fr)]">
            <div class="space-y-4">
                <section class="card" aria-labelledby="help-loop">
                    <h2 id="help-loop" class="text-lg font-semibold text-ink-strong">The turn loop</h2>
                    <p class="mt-1 text-sm text-ink-muted">One turn, one committed action. Everything else is a record of it.</p>
                    <ol class="mt-2 list-decimal space-y-1 pl-5 text-sm text-ink">
                        <li>Open a career from <Link href="/training-runs" class="underline">Careers</Link>. Turn, Energy, Mood and the next obligation are in the first screen.</li>
                        <li>Pick this turn's action: training, a race, an event or a rest. Each one is a decision you record, not a result the tool guesses.</li>
                        <li>Record the outcome. The turn is written only after the action resolves.</li>
                        <li>The timeline keeps every turn with its deltas, so the run can be read back.</li>
                    </ol>
                    <p class="mt-3 rounded-md border border-dashed border-rule bg-raised px-3 py-2 text-sm text-ink-muted">
                        <span aria-hidden="true" class="mr-1">○</span>
                        <span>
                            <b class="text-ink-strong">Undo.</b> A committed turn cannot be taken back from
                            this build: there is no Undo last turn control. Correct a wrong value on the
                            screen that owns it. This is stated as a gap rather than drawn as a button that
                            would do nothing.
                        </span>
                    </p>
                </section>

                <section class="card" aria-labelledby="help-keys">
                    <h2 id="help-keys" class="text-lg font-semibold text-ink-strong">Keyboard</h2>
                    <p class="mt-1 text-sm text-ink-muted">The screens are operable without a pointer.</p>
                    <table class="mt-2 w-full border-collapse text-sm">
                        <tbody>
                            <tr v-for="entry in keys" :key="entry.key" class="border-b border-rule last:border-b-0">
                                <th scope="row" class="w-40 py-2 pr-3 text-left">
                                    <kbd class="rounded border border-rule bg-sunken px-1.5 py-0.5 font-mono text-xs text-ink-strong">{{ entry.key }}</kbd>
                                </th>
                                <td class="py-2 text-ink">{{ entry.does }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section class="card" aria-labelledby="help-tags">
                    <h2 id="help-tags" class="text-lg font-semibold text-ink-strong">What the tags mean</h2>
                    <p class="mt-1 text-sm text-ink-muted">Only exceptions are marked. An unmarked figure is confirmed.</p>
                    <table class="mt-2 w-full border-collapse text-sm">
                        <tbody>
                            <tr v-for="tag in tags" :key="tag.name" class="border-b border-rule last:border-b-0">
                                <th scope="row" class="w-40 py-2 pr-3 text-left font-semibold text-ink-strong">
                                    <span aria-hidden="true" class="mr-1 font-mono">{{ tag.mark }}</span>{{ tag.name }}
                                </th>
                                <td class="py-2 text-ink">{{ tag.meaning }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="mt-3 rounded-md border border-dashed border-rule bg-raised px-3 py-2 text-sm text-ink-muted">
                        <span aria-hidden="true" class="mr-1">○</span>
                        <span>
                            Absence is a normal result. A field the importer cannot find stays empty and
                            prints N/A with its reason. Nothing is filled with a default, because a default
                            is an invention wearing a number.
                        </span>
                    </p>
                </section>
            </div>

            <div class="space-y-4">
                <section class="card" aria-labelledby="help-source">
                    <h2 id="help-source" class="text-lg font-semibold text-ink-strong">Where the numbers come from</h2>
                    <p class="mt-1 text-sm text-ink-muted">
                        Every figure on a screen points at a source. The ledger lists them, with the date
                        each was read, so a stale figure can be recognised as stale.
                    </p>
                    <LinkButton href="/database/sources" variant="secondary" class="mt-3">Open the source ledger</LinkButton>
                </section>

                <section class="card" aria-labelledby="help-rules">
                    <h2 id="help-rules" class="text-lg font-semibold text-ink-strong">Rules that are easier to look up</h2>
                    <table class="mt-2 w-full border-collapse text-sm">
                        <tbody>
                            <tr v-for="rule in rules" :key="rule.name" class="border-b border-rule last:border-b-0">
                                <th scope="row" class="w-28 py-2 pr-3 text-left font-semibold text-ink-strong">{{ rule.name }}</th>
                                <td class="py-2 text-ink">{{ rule.fact }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="mt-2 text-xs text-ink-muted">
                        Each row is a link in a built product. Here they are text, because there is no
                        screen behind them yet and an inert control that looks live is a defect.
                    </p>
                </section>

                <section class="card" aria-labelledby="help-wrong">
                    <h2 id="help-wrong" class="text-lg font-semibold text-ink-strong">If something looks wrong</h2>
                    <table class="mt-2 w-full border-collapse text-sm">
                        <tbody>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-2 pr-3 text-left font-semibold text-ink-strong">A figure is marked Unknown</th>
                                <td class="py-2 text-ink">
                                    The tool was never told it.
                                    <Link href="/training-runs/import" class="underline">Import a run</Link>
                                    shows which fields a parse could not find before anything is written.
                                </td>
                            </tr>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-2 pr-3 text-left font-semibold text-ink-strong">A turn count disagrees</th>
                                <td class="py-2 text-ink">
                                    An import records a position, not a history. The preview states that
                                    consequence before the commit.
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="py-2 pr-3 text-left font-semibold text-ink-strong">A date looks old</th>
                                <td class="py-2 text-ink">
                                    <Link href="/database/sources" class="underline">Check the source</Link>
                                    before assuming the tool is right.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

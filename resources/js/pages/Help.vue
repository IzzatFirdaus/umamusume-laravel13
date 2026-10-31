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
 *
 * HFW-OVERRIDE (keyboard: keys) - `help.html:93-96` lists 1-5, Enter, Esc and Tab as a guided-flow key
 * map. Only Tab/Shift+Tab, Enter/Space, Esc (the mobile More disclosure) and the skip link are wired,
 * so the table names those; printing 1-5 would document controls that do nothing.
 *
 * HFW-OVERRIDE (rules: career length) - the sixth row states the 72-turn grid the app enforces
 * (`CareerCalendar::TURNS_PER_CAREER`); `help.html:132-136` draws five.
 * HFW-OVERRIDE (rules: deck) - the row prints the sourced rule only: a deck holds six support-card slots
 * and two copies of one card cannot be equipped together (`docs/UMAMUSUME_REFERENCE.md:533`). The
 * sentence this page used to carry, "Five from your own cards and one guest rental", is not in any
 * source: a rental is a parent, not a deck slot, so it is deleted rather than footnoted.
 * HFW-OVERRIDE (rules: sparks) - the row names the four `[Global]` spark colours, which is section 1.5.2
 * at `:651`; the mock's row carries only Blue and Pink.
 * HFW-OVERRIDE (rows: diagnostics) - the fourth diagnostic row is this build's: "A date looks old" sends
 * the reader to the source ledger, which is the page's own answer to a stale figure.
 * HFW-OVERRIDE (copy: import) - CSV import persists each supplied turn, not just a current position, so
 * the disagreeing-turn-count row says what this build does.
 * HFW-OVERRIDE (copy: undo note) - the mock describes a reversible turn in one breath and disowns the
 * control in the next; this page states the absence once, in the present tense.
 * HFW-OVERRIDE (copy: keyboard sub), (copy: tags note) and (copy: rules note) - the three sentences the
 * mock writes about itself: "the guided flow" for a table that covers the shell, a bare "stays empty"
 * where the build names the channel, and "this is a static wireframe" beside a decision id. Shipped copy
 * describes the build, never the artefact, and never cites a repository identifier.
 * HFW-OVERRIDE (style: button primitive) - the mock's two actions are `.btn`, a class this set never
 * ported (the Shop items pager records the same gap). The app's button is `LinkButton`, so the ledger
 * door keeps the component's weight and padding rather than matching the mock on one instance.
 *
 * Laid out as the mock draws it: the two columns are plain `div`s and the cards inside them are
 * `div.card role="region"`, separated by the ported `.card + .card`; the card name is a bare `h2` and
 * the subordinate line is `p.sub`, both styled by their ported rules rather than by utilities, so a
 * card's children pair with a card's children.
 */
import AppLayout from '../layouts/AppLayout.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import { LinkButton } from '../components/Form';
import { Head, Link } from '@inertiajs/vue3';

// The trust vocabulary, in the order a Trainer meets it. `confirmed` is deliberately absent: an
// unmarked figure IS the confirmed state, which is why the card says so above the table.
const tags: { mark: string; name: string; variant: string; meaning: string }[] = [
    { mark: '∑', variant: 'trust-calculated', name: 'Calculated', meaning: 'The tool derived it from values you entered. The rule is stated beside it.' },
    { mark: '~', variant: 'trust-estimated', name: 'Estimated', meaning: 'An approximate figure, or one resting on a single source.' },
    { mark: '?', variant: 'trust-unknown', name: 'Unknown', meaning: 'Not recorded, or not published. It is not a zero.' },
];

// The five rule rows and the career length the app enforces. The mood row prints the client strings as
// `lang/en/uma.php` holds them (`mood_great` is 'GREAT', and the four after it), because that row is
// quoting what the panel renders, not a wiki gloss for it.
const rules: { name: string; fact: string }[] = [    { name: 'Energy', fact: '50 is the sourced advisory line. Below it the failure curve changes character rather than degrading evenly.' },
    { name: 'Mood', fact: 'Five tiers. GREAT +20%, GOOD +10%, NORMAL 0, BAD −10%, AWFUL −20% on training yield.' },
    { name: 'Sparks', fact: 'Blue is the stat spark, Pink the aptitude spark, Green the unique-skill hint and White the skill hint. The cap raise is a separate mechanic.' },
    { name: 'Bond', fact: '0 to 100. A card turns orange at 80, which is where Friendship Training becomes available.' },
    { name: 'Deck', fact: 'Six slots. Two copies of one card cannot be equipped together.' },
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

        <div class="grid items-start gap-3.5 g-side">
            <div>
                <div class="card" role="region" aria-labelledby="help-loop">
                    <h2 id="help-loop">The turn loop</h2>
                    <p class="sub">One turn, one committed action. Everything else is a record of it.</p>
                    <ol class="list-decimal space-y-1 pl-5 text-sm text-ink">
                        <li>Open a career from <Link href="/training-runs" class="underline">Careers</Link>. Turn, Energy, Mood and the next obligation are in the first screen.</li>
                        <li>Pick this turn's action: training, a race, an event or a rest. Each one is a decision you record, not a result the tool guesses.</li>
                        <li>Record the outcome. The turn is written only after the action resolves.</li>
                        <li>The timeline keeps every turn with its deltas, so the run can be read back.</li>
                    </ol>
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span>
                            <b>Undo.</b> A committed turn cannot be taken back from
                            this build: there is no Undo last turn control. Correct a wrong value on the
                            screen that owns it. This is stated as a gap rather than drawn as a button that
                            would do nothing.
                        </span>
                    </p>
                </div>

                <div class="card" role="region" aria-labelledby="help-keys">
                    <h2 id="help-keys">Keyboard</h2>
                    <p class="sub">The screens are operable without a pointer.</p>
                    <table class="t" aria-label="Keyboard controls this build wires">
                        <tbody>
                            <tr v-for="entry in keys" :key="entry.key">
                                <th scope="row" class="w-40">
                                    <kbd class="rounded border border-rule bg-sunken px-1.5 py-0.5 font-mono text-xs text-ink-strong">{{ entry.key }}</kbd>
                                </th>
                                <td>{{ entry.does }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card" role="region" aria-labelledby="help-tags">
                    <h2 id="help-tags">What the tags mean</h2>
                    <p class="sub">Only exceptions are marked. An unmarked figure is confirmed.</p>
                    <table class="t" aria-label="The three provenance marks this build prints">
                        <tbody>
                            <tr v-for="tag in tags" :key="tag.name">
                                <th scope="row" class="w-40">
                                    <span :class="['trust', tag.variant]"><span aria-hidden="true" class="mr-1 font-mono">{{ tag.mark }}</span>{{ tag.name }}</span>
                                </th>
                                <td>{{ tag.meaning }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span>
                            Absence is a normal result. A field the importer cannot find stays empty and
                            prints N/A with its reason. Nothing is filled with a default, because a default
                            is an invention wearing a number.
                        </span>
                    </p>
                </div>
            </div>

            <div>
                <div class="card" role="region" aria-labelledby="help-source">
                    <h2 id="help-source">Where the numbers come from</h2>
                    <p class="sub">
                        The source ledger lists fetched datasets and transcribed references. Its date
                        notes explain when the data was read and what needs checking again.
                    </p>
                    <LinkButton href="/database/sources" variant="secondary">Open the source ledger</LinkButton>
                </div>

                <div class="card" role="region" aria-labelledby="help-rules">
                    <h2 id="help-rules">Rules that are easier to look up</h2>
                    <table class="t" aria-label="Rules a Trainer can look up instead of infer">
                        <tbody>
                            <tr v-for="rule in rules" :key="rule.name">
                                <th scope="row" class="w-28">{{ rule.name }}</th>
                                <td>{{ rule.fact }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- The wireframe's note in this card (`help.html:139`): the rows are text, and
                         saying why is the honest alternative to an inert control. -->
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span>
                            Each row states the rule in place; there is no target page behind it, so the
                            row is text rather than a link. An inert control that looks live is the defect
                            this avoids.
                        </span>
                    </p>
                </div>

                <div class="card" role="region" aria-labelledby="help-wrong">
                    <h2 id="help-wrong">If something looks wrong</h2>
                    <table class="t" aria-label="What to check when a figure or a state looks wrong">
                        <tbody>
                            <tr>
                                <th scope="row">A figure is marked Unknown</th>
                                <td>
                                    The tool was never told it.
                                    <Link href="/training-runs/import" class="underline">Import a run</Link>
                                    shows which fields a parse could not find before anything is written.
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">A race looks unavailable</th>
                                <td>
                                    Its row states the gate: the fan requirement and the maiden gate are
                                    printed rather than hidden.
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">A turn count disagrees</th>
                                <td>
                                    A CSV import saves only the turn rows you supply. Check their turn
                                    numbers in the preview before importing.
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">A date looks old</th>
                                <td>
                                    <Link href="/database/sources" class="underline">Check the source</Link>
                                    before assuming the tool is right.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

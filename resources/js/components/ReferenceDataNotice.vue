<script setup lang="ts">
import { ref } from 'vue';

/*
 * One named state for a fresh install, shown where empty catalogues would otherwise read as broken
 * (UX-23). Three zero tiles and a bare "0 trainees are catalogued" told a new Trainer nothing about
 * why the app looks empty; this states the fact, names the fix, and puts the command on the
 * clipboard so the fix is one paste away.
 *
 * The copy is a project convention: a named absence with its remedy, never a blank (`NRM-2`), and
 * the command is `AGENTS.md` §10's own, not a suggestion of this component's. The Copied state is
 * per-press and expires: a stale "Copied" after a failed paste would be the false feedback
 * `ADR-0007` exists to prevent.
 */
const props = withDefaults(
    defineProps<{
        /** The artisan command that fills the catalogues. */
        command?: string;
        /** A one-line reason, when the host screen wants to name what is missing specifically. */
        reason?: string;
    }>(),
    { command: 'php artisan uma:fetch', reason: 'The catalogues are empty because no source has been fetched yet.' },
);

const copied = ref(false);

function copy(): void {
    void navigator.clipboard.writeText(props.command).then(() => {
        copied.value = true;

        window.setTimeout(() => {
            copied.value = false;
        }, 2000);
    });
}
</script>

<template>
    <section
        aria-labelledby="reference-data-heading"
        class="card"
        data-region="reference-data-notice"
    >
        <h2 id="reference-data-heading" class="text-base">Reference data not loaded</h2>

        <p class="mt-1.5 text-sm text-ink-muted">{{ reason }}</p>

        <div class="mt-3 flex flex-wrap items-center gap-3">
            <code class="rounded-md border border-rule bg-sunken px-3 py-2 font-mono text-sm text-ink-strong">{{ command }}</code>
            <button
                type="button"
                class="inline-flex min-h-11 items-center rounded-md border border-raised bg-raised px-4 text-sm font-semibold text-ink hover:bg-panel"
                @click="copy"
            >
                {{ copied ? 'Copied' : 'Copy command' }}
            </button>
        </div>

        <p class="mt-2 text-xs text-ink-muted">
            The command fills the trainee, skill and support-card catalogues from the sources the
            fetch engine allowlists. Nothing is fetched by browsing.
        </p>
    </section>
</template>

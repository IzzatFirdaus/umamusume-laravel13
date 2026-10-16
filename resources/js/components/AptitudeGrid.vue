<script setup lang="ts">
// The ten aptitude letters in the export's own element order: surface, the four distances,
// the four running styles. The letters are the trainee's, never a card's (ADR-0008). Two rows,
// because "how does she run" and "how fast does she go" are separate reads.
defineProps<{ aptitudes: Record<string, string> }>();

const rows: { label: string; key: string }[][] = [
    [
        { label: 'Turf', key: 'turf' },
        { label: 'Dirt', key: 'dirt' },
    ],
    [
        { label: 'Sprint', key: 'sprint' },
        { label: 'Mile', key: 'mile' },
        { label: 'Medium', key: 'medium' },
        { label: 'Long', key: 'long' },
    ],
    [
        // The running styles are the Global client's words, and the case is the client's too
        // (`lang/en/uma.php:109-112` owns the vocabulary): these were hand-typed lowercase and read
        // as a second spelling of the four terms every other surface prints.
        { label: 'Front Runner', key: 'front_runner' },
        { label: 'Pace Chaser', key: 'pace_chaser' },
        { label: 'Late Surger', key: 'late_surger' },
        { label: 'End Closer', key: 'end_closer' },
    ],
];
</script>

<template>
    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm @3xl:grid-cols-4">
        <template v-for="(row, index) in rows" :key="index">
            <div
                v-for="cell in row"
                :key="cell.key"
                class="flex items-baseline gap-2 border-b border-rule pb-1"
            >
                <dt class="text-ink-muted">{{ cell.label }}</dt>
                <dd class="font-mono font-bold text-ink-strong">{{ aptitudes[cell.key] }}</dd>
            </div>
        </template>
    </dl>
</template>

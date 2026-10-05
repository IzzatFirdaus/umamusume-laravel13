<script setup lang="ts">
// The glyph run is the readout (DesignTokensTest pins the token set; G-47 bans white on a
// light fill). role="img" + aria-label turns the glyphs into a name.
defineProps<{ label: string; stars: string }>();
</script>

<template>
    <span class="font-mono text-xs font-bold text-ink" role="img" :aria-label="label">
        <span aria-hidden="true">{{ stars }}</span>
    </span>
</template>

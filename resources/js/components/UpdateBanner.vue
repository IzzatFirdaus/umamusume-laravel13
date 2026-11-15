<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const available = ref(false);
const remoteVersion = ref<string | null>(null);
const downloadUrl = ref<string | null>(null);

onMounted(async () => {
    try {
        const { data } = await axios.get('/updates/check');
        if (data.available) {
            available.value = true;
            remoteVersion.value = data.version;
            downloadUrl.value = data.url;
        }
    } catch {
        // Feed unreachable; banner stays inert.
    }
});
</script>

<template>
    <div
        v-if="available"
        class="mb-4 rounded border border-green-line bg-green-tint px-3 py-2 text-sm text-ink"
        role="status"
    >
        A new version ({{ remoteVersion }}) is available.
        <a v-if="downloadUrl" :href="downloadUrl" class="font-bold text-pick-line hover:underline" target="_blank" rel="noopener">
            Download
        </a>
    </div>
</template>
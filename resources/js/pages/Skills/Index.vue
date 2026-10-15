<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import SkillCatalog from '../../components/skills/SkillCatalog.vue';
import { Head } from '@inertiajs/vue3';

interface Skill {
    id: number;
    name: string;
    name_ja: string | null;
    is_unique: boolean;
    type: string | null;
    sp_cost: number | null;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

const props = defineProps<{
    skills: Paginator<Skill>;
    search: string | null;
    searchKey: string | null;
    type: string | null;
    types: string[];
    unspecifiedType: string;
    unique: boolean;
    totalCount: number;
    askedFor: string;
}>();
</script>

<template>
    <AppLayout>
        <Head title="Skill search" />
        <template #eyebrow>Reference</template>
        <template #title>Skill search</template>

        <SkillCatalog
            :skills="props.skills"
            :search="props.search"
            :search-key="props.searchKey"
            :type="props.type"
            :types="props.types"
            :unspecified-type="props.unspecifiedType"
            :unique="props.unique"
            :total-count="props.totalCount"
            :asked-for="props.askedFor"
            filter-path="/skills"
        />
    </AppLayout>
</template>

<script setup lang="ts">
/*
 * The Database hub's Skills area (SCREEN-023, plan §8 D17).
 *
 * The skill search, on the Database URL. The same shared list body the skill search mounts, behind
 * `SkillController::databaseIndex`. See `Trainees.vue` for why this is a shared screen rather than a
 * redirect to `/skills`.
 */
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
        <Head title="Skills" />
        <template #title>Skills</template>

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
            filter-path="/database/skills"
        />
    </AppLayout>
</template>

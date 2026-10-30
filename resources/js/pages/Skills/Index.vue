<script setup lang="ts">
/*
 * Skill search (Screen D, PRD FR-D-2). The page owns the head band and mounts the catalogue body that
 * `/database/skills` also renders; see `components/skills/SkillCatalog.vue` for the table's rules and the
 * two HFW overrides (no Distance facet, name-only search).
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import SkillCatalog from '../../components/skills/SkillCatalog.vue';
import { Head } from '@inertiajs/vue3';

interface Skill {
    id: number;
    name: string;
    name_ja: string | null;
    is_unique: boolean;
    type: string | null;
    sp_cost: number | null;
    conditions: (string | null)[];
}

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
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
    <AppLayout :breadcrumb="[{ label: 'Library' }, { label: 'Skills' }]">
        <Head title="Skill search" />
        <template #head>
            <PageHeader
                eyebrow="Skills"
                title="Skill search"
                :description="`${props.totalCount} skills available on Global. The condition column is the source's own engine data, not a client paraphrase.`"
            />
        </template>

        <DatabaseNav />

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

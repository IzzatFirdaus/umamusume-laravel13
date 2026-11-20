<script setup lang="ts">
/* Library entry point for the shared skill catalog. */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import SkillCatalog, { type Paginator, type Skill } from '../../components/skills/SkillCatalog.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    skills: Paginator<Skill>;
    search: string | null;
    searchKey: string | null;
    type: string | null;
    types: string[];
    unspecifiedType: string;
    distance: number | null;
    scope: string;
    scopes: Record<string, string>;
    unique: boolean;
    totalCount: number;
    askedFor: string;
    rangeOpCount: number;
    /** Full-table counts by release status, for the catalogue header. Scope-invariant by design. */
    release_status_counts: { global: number; japan_only: number; other: number };
}>();
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Library' }, { label: 'Skills' }]">
        <Head title="Skill search" />
        <template #head>
            <PageHeader
                eyebrow="Skills"
                title="Skill search"
                description="Find skills by name, effect, type or distance."
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
            :distance="props.distance"
            :scope="props.scope"
            :scopes="props.scopes"
            :unique="props.unique"
            :total-count="props.totalCount"
            :asked-for="props.askedFor"
            :range-op-count="props.rangeOpCount"
            :release_status_counts="props.release_status_counts"
            filter-path="/skills"
        />
    </AppLayout>
</template>

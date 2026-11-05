<script setup lang="ts">
/*
 * The Database hub's Skills area (SCREEN-023, plan §8 D17).
 *
 * The skill search, on the Database URL. The same shared table body the skill search mounts, behind
 * `SkillController::databaseIndex`. See `Trainees.vue` for why this is a shared screen rather than a
 * redirect to `/skills`.
 */
import AppLayout from '../../layouts/AppLayout.vue';
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
    <AppLayout :breadcrumb="[{ label: 'Database', href: '/database' }, { label: 'Skills' }]">
        <Head title="Skills" />
        <template #title>Skills</template>

        <DatabaseNav />

        <SkillCatalog
            heading="Skill catalog"
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
            filter-path="/database/skills"
        />
    </AppLayout>
</template>

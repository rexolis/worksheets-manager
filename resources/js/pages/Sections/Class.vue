<script setup lang="ts">
import { Head, Link, setLayoutProps, usePage } from '@inertiajs/vue3';
import { ChevronRight, Plus, Users } from '@lucide/vue';
import { computed, ref } from 'vue';
import SectionFormDialog from '@/components/SectionFormDialog.vue';
import SectionRowActions from '@/components/SectionRowActions.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    show as sectionShow,
    showClass as sectionClassRoute,
} from '@/routes/sections';

type WorksheetClassItem = {
    id: number;
    name: string;
    slug: string;
};

type SectionItem = {
    id: number;
    name: string;
    section_type: string;
    class_code: string;
    date_start: string;
    date_end: string;
    status: string;
    teacher_ids: number[];
    worksheet_class: WorksheetClassItem;
};

type TeacherItem = {
    id: number;
    name: string;
    email: string;
};

const props = defineProps<{
    worksheetClass: WorksheetClassItem;
    sections: SectionItem[];
    teachers: TeacherItem[];
}>();

const page = usePage();
const createDialogOpen = ref(false);

const isAdmin = computed(() => page.props.auth.user?.is_admin === true);

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Sections',
        },
        {
            title: props.worksheetClass.name,
            href: sectionClassRoute(props.worksheetClass.slug),
        },
    ],
});

function formatDate(date: string): string {
    return new Date(date + 'T00:00:00').toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head :title="worksheetClass.name" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold">{{ worksheetClass.name }}</h1>
                <p class="text-sm text-muted-foreground">
                    Sections for this class.
                </p>
            </div>

            <SectionFormDialog
                v-if="isAdmin"
                v-model:open="createDialogOpen"
                :worksheet-class="worksheetClass"
                :teachers="teachers"
            >
                <Button data-test="create-section-button">
                    <Plus class="size-4" />
                    Add section
                </Button>
            </SectionFormDialog>
        </div>

        <div
            v-if="sections.length === 0"
            class="flex min-h-96 flex-1 flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
        >
            <div class="rounded-full bg-muted p-4">
                <Users class="size-8 text-muted-foreground" />
            </div>

            <div class="space-y-1">
                <h2 class="text-lg font-semibold">No sections yet</h2>
                <p class="max-w-md text-sm text-muted-foreground">
                    Sections will appear here once they are available for this
                    class.
                </p>
            </div>
        </div>

        <div
            v-else
            class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <div
                class="grid gap-x-4 border-b border-sidebar-border/70 px-4 py-2 text-xs font-medium text-muted-foreground dark:border-sidebar-border"
                :class="
                    isAdmin
                        ? 'grid-cols-[minmax(0,1fr)_auto_11rem_6.5rem] sm:grid-cols-[minmax(0,1fr)_8rem_12rem_7.5rem_7.5rem_1rem_6.5rem]'
                        : 'grid-cols-[minmax(0,1fr)_auto_11rem_1rem] sm:grid-cols-[minmax(0,1fr)_8rem_12rem_7.5rem_7.5rem_1rem]'
                "
            >
                <span>Name</span>
                <span>Type</span>
                <span>Code</span>
                <span class="hidden sm:block">Start</span>
                <span class="hidden sm:block">End</span>
                <span class="sr-only">Open</span>
                <span v-if="isAdmin" class="text-right">Actions</span>
            </div>

            <ul
                class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border"
            >
                <li
                    v-for="section in sections"
                    :key="section.id"
                    class="grid items-center gap-x-4 gap-y-1 px-4 py-3 text-sm transition-colors hover:bg-muted/50"
                    :class="
                        isAdmin
                            ? 'grid-cols-[minmax(0,1fr)_auto_11rem_6.5rem] sm:grid-cols-[minmax(0,1fr)_8rem_12rem_7.5rem_7.5rem_1rem_6.5rem]'
                            : 'grid-cols-[minmax(0,1fr)_auto_11rem_auto] sm:grid-cols-[minmax(0,1fr)_8rem_12rem_7.5rem_7.5rem_auto]'
                    "
                >
                    <Link
                        :href="
                            sectionShow({
                                worksheetClass: worksheetClass.slug,
                                section: section.class_code,
                            })
                        "
                        class="contents"
                        prefetch
                    >
                        <div class="min-w-0">
                            <div class="flex min-w-0 items-center gap-2">
                                <p class="truncate font-medium">
                                    {{ section.name }}
                                </p>
                                <Badge
                                    v-if="section.status === 'archived'"
                                    variant="secondary"
                                >
                                    Archived
                                </Badge>
                            </div>
                            <p class="text-muted-foreground sm:hidden">
                                {{ formatDate(section.date_start) }} –
                                {{ formatDate(section.date_end) }}
                            </p>
                        </div>
                        <span class="truncate text-muted-foreground">
                            {{ section.section_type }}
                        </span>
                        <span class="font-medium tabular-nums">
                            {{ section.class_code }}
                        </span>
                        <span
                            class="hidden whitespace-nowrap text-muted-foreground sm:block"
                        >
                            {{ formatDate(section.date_start) }}
                        </span>
                        <span
                            class="hidden whitespace-nowrap text-muted-foreground sm:block"
                        >
                            {{ formatDate(section.date_end) }}
                        </span>
                        <ChevronRight
                            class="size-4 shrink-0 self-center text-muted-foreground"
                        />
                    </Link>

                    <SectionRowActions
                        v-if="isAdmin"
                        :section="section"
                        :teachers="teachers"
                    />
                </li>
            </ul>
        </div>
    </div>
</template>

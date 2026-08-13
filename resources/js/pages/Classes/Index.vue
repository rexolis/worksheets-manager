<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { BookOpen, GraduationCap, Plus } from '@lucide/vue';
import { ref } from 'vue';
import EnrollClassDialog from '@/components/EnrollClassDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { classes as classesRoute } from '@/routes';

type SectionItem = {
    id: number;
    name: string;
    section_type: string;
    class_code: string;
    date_start: string;
    date_end: string;
    status: 'pending' | 'approved';
};

type WorksheetClassItem = {
    id: number;
    name: string;
    slug: string;
    sections: SectionItem[];
};

defineProps<{
    classes: WorksheetClassItem[];
}>();

const enrollDialogOpen = ref(false);

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Classes',
            href: classesRoute(),
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

function statusLabel(status: SectionItem['status']): string {
    return status === 'approved' ? 'Approved' : 'Pending';
}
</script>

<template>
    <Head title="Classes" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold">Classes</h1>
                <p class="text-sm text-muted-foreground">
                    Enroll with a class code and track your pending or approved
                    sections.
                </p>
            </div>

            <EnrollClassDialog v-model:open="enrollDialogOpen">
                <Button data-test="enroll-class-button">
                    <Plus class="size-4" />
                    Enroll in a class
                </Button>
            </EnrollClassDialog>
        </div>

        <div
            v-if="classes.length === 0"
            class="flex min-h-64 flex-1 flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
        >
            <div class="rounded-full bg-muted p-4">
                <GraduationCap class="size-8 text-muted-foreground" />
            </div>

            <div class="space-y-1">
                <h2 class="text-lg font-semibold">No classes yet</h2>
                <p class="max-w-md text-sm text-muted-foreground">
                    Use Enroll in a class to request enrollment with a class
                    code.
                </p>
            </div>
        </div>

        <div v-else class="space-y-6">
            <section
                v-for="worksheetClass in classes"
                :key="worksheetClass.id"
                class="space-y-3"
            >
                <div class="flex items-center gap-2">
                    <BookOpen class="size-4 text-muted-foreground" />
                    <h2 class="text-sm font-medium">
                        {{ worksheetClass.name }}
                    </h2>
                </div>

                <div
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <div
                        class="grid grid-cols-[minmax(0,1fr)_auto] gap-x-4 border-b border-sidebar-border/70 px-4 py-2 text-xs font-medium text-muted-foreground sm:grid-cols-[minmax(0,1fr)_8rem_12rem_7.5rem] dark:border-sidebar-border"
                    >
                        <span>Section</span>
                        <span class="hidden sm:inline">Type</span>
                        <span class="hidden sm:inline">Class code</span>
                        <span class="text-right sm:text-left">Status</span>
                    </div>

                    <ul
                        class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border"
                    >
                        <li
                            v-for="section in worksheetClass.sections"
                            :key="section.id"
                            class="grid grid-cols-[minmax(0,1fr)_auto] gap-x-4 px-4 py-3 text-sm sm:grid-cols-[minmax(0,1fr)_8rem_12rem_7.5rem]"
                        >
                            <div class="min-w-0">
                                <p class="truncate font-medium">
                                    {{ section.name }}
                                </p>
                                <p class="text-muted-foreground sm:hidden">
                                    {{ section.class_code }} ·
                                    {{ formatDate(section.date_start) }} –
                                    {{ formatDate(section.date_end) }}
                                </p>
                            </div>
                            <span
                                class="hidden truncate text-muted-foreground sm:inline"
                            >
                                {{ section.section_type }}
                            </span>
                            <span
                                class="hidden font-medium tabular-nums sm:inline"
                            >
                                {{ section.class_code }}
                            </span>
                            <div
                                class="flex items-start justify-end sm:justify-start"
                            >
                                <Badge
                                    :variant="
                                        section.status === 'approved'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{ statusLabel(section.status) }}
                                </Badge>
                            </div>
                        </li>
                    </ul>
                </div>
            </section>
        </div>
    </div>
</template>

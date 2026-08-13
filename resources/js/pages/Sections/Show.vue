<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { ArrowLeft, GraduationCap } from '@lucide/vue';
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
    worksheet_class: WorksheetClassItem;
};

type StudentItem = {
    id: number;
    name: string;
    email: string;
    status: 'pending' | 'approved';
};

const props = defineProps<{
    worksheetClass: WorksheetClassItem;
    section: SectionItem;
    students: StudentItem[];
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Sections',
        },
        {
            title: props.worksheetClass.name,
            href: sectionClassRoute(props.worksheetClass.slug),
        },
        {
            title: props.section.name,
            href: sectionShow({
                worksheetClass: props.worksheetClass.slug,
                section: props.section.class_code,
            }),
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

function statusLabel(status: StudentItem['status']): string {
    return status === 'approved' ? 'Approved' : 'Pending';
}
</script>

<template>
    <Head :title="`${section.name} · ${worksheetClass.name}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-start gap-3">
            <Button variant="outline" size="icon" class="shrink-0" as-child>
                <Link :href="sectionClassRoute(worksheetClass.slug)">
                    <ArrowLeft class="size-4" />
                    <span class="sr-only">Back</span>
                </Link>
            </Button>

            <div class="min-w-0 flex-1 space-y-1">
                <p class="text-sm text-muted-foreground">
                    {{ worksheetClass.name }}
                </p>
                <h1 class="text-xl font-semibold">{{ section.name }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ section.section_type }} · {{ section.class_code }} ·
                    {{ formatDate(section.date_start) }} –
                    {{ formatDate(section.date_end) }}
                </p>
            </div>
        </div>

        <div class="space-y-3">
            <h2 class="text-sm font-medium">Enrolled students</h2>

            <div
                v-if="students.length === 0"
                class="flex min-h-64 flex-1 flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
            >
                <div class="rounded-full bg-muted p-4">
                    <GraduationCap class="size-8 text-muted-foreground" />
                </div>

                <div class="space-y-1">
                    <h3 class="text-lg font-semibold">No students yet</h3>
                    <p class="max-w-md text-sm text-muted-foreground">
                        Enrolled students for this section will appear here.
                    </p>
                </div>
            </div>

            <div
                v-else
                class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div
                    class="grid grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_6.5rem] gap-x-4 border-b border-sidebar-border/70 px-4 py-2 text-xs font-medium text-muted-foreground dark:border-sidebar-border"
                >
                    <span>Name</span>
                    <span>Email</span>
                    <span>Status</span>
                </div>

                <ul
                    class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border"
                >
                    <li
                        v-for="student in students"
                        :key="student.id"
                        class="grid grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_6.5rem] gap-x-4 px-4 py-3 text-sm"
                    >
                        <span class="truncate font-medium">{{
                            student.name
                        }}</span>
                        <span class="truncate text-muted-foreground">{{
                            student.email
                        }}</span>
                        <div>
                            <Badge
                                :variant="
                                    student.status === 'approved'
                                        ? 'default'
                                        : 'secondary'
                                "
                            >
                                {{ statusLabel(student.status) }}
                            </Badge>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

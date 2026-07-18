<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { BookCheck, ChevronRight } from '@lucide/vue';
import { worksheets as worksheetsIndex } from '@/routes';
import {
    showClass as worksheetClassRoute,
    subject as worksheetSubject,
} from '@/routes/worksheets';

type SubjectItem = {
    id: number;
    name: string;
    slug: string;
};

type NamedSlug = {
    id: number;
    name: string;
    slug: string;
};

const props = defineProps<{
    worksheetClass: NamedSlug;
    subjects: SubjectItem[];
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Worksheets',
            href: worksheetsIndex(),
        },
        {
            title: props.worksheetClass.name,
            href: worksheetClassRoute(props.worksheetClass.slug),
        },
    ],
});
</script>

<template>
    <Head :title="worksheetClass.name" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="space-y-1">
            <h1 class="text-xl font-semibold">{{ worksheetClass.name }}</h1>
            <p class="text-sm text-muted-foreground">
                Browse subjects for this class.
            </p>
        </div>

        <div
            v-if="subjects.length === 0"
            class="flex min-h-96 flex-1 flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
        >
            <div class="rounded-full bg-muted p-4">
                <BookCheck class="size-8 text-muted-foreground" />
            </div>

            <div class="space-y-1">
                <h2 class="text-lg font-semibold">No subjects yet</h2>
                <p class="max-w-md text-sm text-muted-foreground">
                    Subjects will appear here once worksheets are available for
                    this class.
                </p>
            </div>
        </div>

        <ul
            v-else
            class="divide-y divide-sidebar-border/70 overflow-hidden rounded-xl border border-sidebar-border/70 dark:divide-sidebar-border dark:border-sidebar-border"
        >
            <li v-for="subjectItem in subjects" :key="subjectItem.id">
                <Link
                    :href="
                        worksheetSubject({
                            worksheetClass: worksheetClass.slug,
                            subject: subjectItem.slug,
                        })
                    "
                    class="flex items-center justify-between gap-3 px-4 py-3 text-sm transition-colors hover:bg-muted/50"
                    prefetch
                >
                    <span>{{ subjectItem.name }}</span>
                    <ChevronRight
                        class="size-4 shrink-0 text-muted-foreground"
                    />
                </Link>
            </li>
        </ul>
    </div>
</template>

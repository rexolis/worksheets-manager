<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookCheck, ChevronRight } from '@lucide/vue';
import { computed } from 'vue';
import {
    showClass as worksheetClassRoute,
    subject as worksheetSubject,
} from '@/routes/worksheets';

type SubjectItem = {
    id: number;
    name: string;
    slug: string;
};

type ClassItem = {
    id: number;
    name: string;
    slug: string;
    subjects: SubjectItem[];
};

const props = defineProps<{
    classes: ClassItem[];
}>();

const hasBrowseContent = computed(() =>
    props.classes.some((worksheetClass) => worksheetClass.subjects.length > 0),
);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Worksheets',
            },
        ],
    },
});
</script>

<template>
    <Head title="Worksheets" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="space-y-1">
            <h1 class="text-xl font-semibold">Worksheets</h1>
            <p class="text-sm text-muted-foreground">
                Browse worksheets by class and subject.
            </p>
        </div>

        <div
            v-if="!hasBrowseContent"
            class="flex min-h-96 flex-1 flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
        >
            <div class="rounded-full bg-muted p-4">
                <BookCheck class="size-8 text-muted-foreground" />
            </div>

            <div class="space-y-1">
                <h2 class="text-lg font-semibold">No worksheets yet</h2>
                <p class="max-w-md text-sm text-muted-foreground">
                    Classes and subjects will appear here once worksheets are
                    available.
                </p>
            </div>
        </div>

        <div v-else class="flex flex-col gap-6">
            <section
                v-for="worksheetClass in classes"
                :key="worksheetClass.id"
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div
                    class="border-b border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <Link
                        :href="worksheetClassRoute(worksheetClass.slug)"
                        class="flex items-center justify-between gap-3 px-4 py-3 transition-colors hover:bg-muted/50"
                        prefetch
                    >
                        <h2 class="font-semibold">{{ worksheetClass.name }}</h2>
                        <ChevronRight
                            class="size-4 shrink-0 text-muted-foreground"
                        />
                    </Link>
                </div>

                <ul
                    v-if="worksheetClass.subjects.length > 0"
                    class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border"
                >
                    <li
                        v-for="subjectItem in worksheetClass.subjects"
                        :key="subjectItem.id"
                    >
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

                <p
                    v-else
                    class="px-4 py-3 text-sm text-muted-foreground"
                >
                    No subjects available for this class.
                </p>
            </section>
        </div>
    </div>
</template>

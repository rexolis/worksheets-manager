<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Users } from '@lucide/vue';

type WorksheetClassItem = {
    id: number;
    name: string;
    slug: string;
};

type SectionItem = {
    id: number;
    name: string;
    class_code: string;
    date_start: string;
    date_end: string;
    worksheet_class: WorksheetClassItem;
};

defineProps<{
    sections: SectionItem[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Sections',
            },
        ],
    },
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
    <Head title="Sections" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="space-y-1">
            <h1 class="text-xl font-semibold">Sections</h1>
            <p class="text-sm text-muted-foreground">
                All sections across worksheet classes.
            </p>
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
                    Sections will appear here once they are available.
                </p>
            </div>
        </div>

        <div
            v-else
            class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <div
                class="grid grid-cols-[1fr_auto] gap-x-4 border-b border-sidebar-border/70 px-4 py-2 text-xs font-medium text-muted-foreground sm:grid-cols-[1.2fr_1.5fr_auto_auto_auto] dark:border-sidebar-border"
            >
                <span>Name</span>
                <span class="hidden sm:block">Class type</span>
                <span>Code</span>
                <span class="hidden sm:block">Start</span>
                <span class="hidden sm:block">End</span>
            </div>

            <ul
                class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border"
            >
                <li
                    v-for="section in sections"
                    :key="section.id"
                    class="grid grid-cols-[1fr_auto] gap-x-4 gap-y-1 px-4 py-3 text-sm sm:grid-cols-[1.2fr_1.5fr_auto_auto_auto]"
                >
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ section.name }}</p>
                        <p class="truncate text-muted-foreground sm:hidden">
                            {{ section.worksheet_class.name }}
                        </p>
                        <p class="text-muted-foreground sm:hidden">
                            {{ formatDate(section.date_start) }} –
                            {{ formatDate(section.date_end) }}
                        </p>
                    </div>
                    <span
                        class="hidden truncate text-muted-foreground sm:block"
                    >
                        {{ section.worksheet_class.name }}
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
                </li>
            </ul>
        </div>
    </div>
</template>

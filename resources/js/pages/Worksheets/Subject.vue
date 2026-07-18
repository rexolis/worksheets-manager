<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { BookCheck } from '@lucide/vue';
import { worksheets as worksheetsIndex } from '@/routes';
import { subject as worksheetSubject } from '@/routes/worksheets';

type NamedSlug = {
    id: number;
    name: string;
    slug: string;
};

type WorksheetItem = {
    id: number;
    number: number;
    title: string;
    subtopic: string | null;
};

const props = defineProps<{
    worksheetClass: NamedSlug;
    subject: NamedSlug;
    worksheets: WorksheetItem[];
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Worksheets',
            href: worksheetsIndex(),
        },
        {
            title: `${props.worksheetClass.name} / ${props.subject.name}`,
            href: worksheetSubject({
                worksheetClass: props.worksheetClass.slug,
                subject: props.subject.slug,
            }),
        },
    ],
});
</script>

<template>
    <Head :title="`${worksheetClass.name} · ${subject.name}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="space-y-1">
            <p class="text-sm text-muted-foreground">
                {{ worksheetClass.name }}
            </p>
            <h1 class="text-xl font-semibold">{{ subject.name }}</h1>
            <p class="text-sm text-muted-foreground">
                {{ worksheets.length }}
                {{ worksheets.length === 1 ? 'worksheet' : 'worksheets' }}
            </p>
        </div>

        <div
            v-if="worksheets.length === 0"
            class="flex min-h-64 flex-1 flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
        >
            <div class="rounded-full bg-muted p-4">
                <BookCheck class="size-8 text-muted-foreground" />
            </div>

            <div class="space-y-1">
                <h2 class="text-lg font-semibold">No worksheets</h2>
                <p class="max-w-md text-sm text-muted-foreground">
                    There are no worksheets for this class and subject yet.
                </p>
            </div>
        </div>

        <div
            v-else
            class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <div
                class="grid grid-cols-[auto_1fr] gap-x-4 border-b border-sidebar-border/70 px-4 py-2 text-xs font-medium text-muted-foreground sm:grid-cols-[4rem_1fr_1fr] dark:border-sidebar-border"
            >
                <span>#</span>
                <span>Title</span>
                <span class="hidden sm:block">Subtopic</span>
            </div>

            <ul
                class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border"
            >
                <li
                    v-for="worksheet in worksheets"
                    :key="worksheet.id"
                    class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 px-4 py-3 text-sm sm:grid-cols-[4rem_1fr_1fr]"
                >
                    <span class="font-medium tabular-nums text-muted-foreground">
                        {{ worksheet.number }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ worksheet.title }}</p>
                        <p
                            v-if="worksheet.subtopic"
                            class="truncate text-muted-foreground sm:hidden"
                        >
                            {{ worksheet.subtopic }}
                        </p>
                    </div>
                    <span
                        class="hidden truncate text-muted-foreground sm:block"
                    >
                        {{ worksheet.subtopic ?? '—' }}
                    </span>
                </li>
            </ul>
        </div>
    </div>
</template>

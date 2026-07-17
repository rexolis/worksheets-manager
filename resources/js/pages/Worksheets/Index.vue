<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { BookCheck } from '@lucide/vue';
import { worksheets } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Worksheets',
                href: worksheets(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Worksheets" />

    <div class="flex h-full flex-1 p-4">
        <div
            class="flex min-h-96 flex-1 flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
        >
            <div class="rounded-full bg-muted p-4">
                <BookCheck class="size-8 text-muted-foreground" />
            </div>

            <div class="space-y-1">
                <h1 class="text-xl font-semibold">Worksheets</h1>
                <p class="max-w-md text-sm text-muted-foreground">
                    Your worksheets will appear here once you create one.
                </p>
            </div>
        </div>
    </div>
</template>

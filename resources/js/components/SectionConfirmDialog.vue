<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    archive as archiveSection,
    destroy as destroySection,
} from '@/actions/App/Http/Controllers/SectionController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    action: 'archive' | 'delete';
    section: {
        id: number;
        name: string;
    };
}>();

const open = defineModel<boolean>('open', { default: false });
const processing = ref(false);

const title = computed(() =>
    props.action === 'archive' ? 'Archive section' : 'Delete section',
);

const description = computed(() =>
    props.action === 'archive'
        ? `Archive ${props.section.name}?`
        : `Delete ${props.section.name}?`,
);

const confirmLabel = computed(() =>
    props.action === 'archive' ? 'Archive' : 'Delete',
);

const confirmTestId = computed(() =>
    props.action === 'archive'
        ? 'confirm-archive-section-button'
        : 'confirm-delete-section-button',
);

function confirm(): void {
    processing.value = true;

    const options = {
        onFinish: () => {
            processing.value = false;
            open.value = false;
        },
    };

    if (props.action === 'archive') {
        router.post(archiveSection.url(props.section.id), {}, options);

        return;
    }

    router.delete(destroySection.url(props.section.id), options);
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button type="button" variant="secondary">Cancel</Button>
                </DialogClose>
                <Button
                    type="button"
                    :variant="action === 'delete' ? 'destructive' : 'default'"
                    :disabled="processing"
                    :data-test="confirmTestId"
                    @click="confirm"
                >
                    {{ confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

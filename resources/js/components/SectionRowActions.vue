<script setup lang="ts">
import { Archive, Pencil, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import SectionConfirmDialog from '@/components/SectionConfirmDialog.vue';
import SectionFormDialog from '@/components/SectionFormDialog.vue';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

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
    section: SectionItem;
    teachers: TeacherItem[];
}>();

const editDialogOpen = ref(false);
const archiveDialogOpen = ref(false);
const deleteDialogOpen = ref(false);

const isArchived = computed(() => props.section.status === 'archived');
</script>

<template>
    <div class="flex items-center justify-end gap-1" @click.stop>
        <TooltipProvider :delay-duration="0">
            <Tooltip>
                <TooltipTrigger as-child>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        data-test="edit-section-button"
                        :aria-label="`Edit ${section.name}`"
                        @click="editDialogOpen = true"
                    >
                        <Pencil class="size-4" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>Edit</TooltipContent>
            </Tooltip>

            <Tooltip v-if="!isArchived">
                <TooltipTrigger as-child>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        data-test="archive-section-button"
                        :aria-label="`Archive ${section.name}`"
                        @click="archiveDialogOpen = true"
                    >
                        <Archive class="size-4" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>Archive</TooltipContent>
            </Tooltip>

            <Tooltip>
                <TooltipTrigger as-child>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        class="text-destructive hover:text-destructive"
                        data-test="delete-section-button"
                        :aria-label="`Delete ${section.name}`"
                        @click="deleteDialogOpen = true"
                    >
                        <Trash2 class="size-4" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>Delete</TooltipContent>
            </Tooltip>
        </TooltipProvider>

        <SectionFormDialog
            v-model:open="editDialogOpen"
            :worksheet-class="section.worksheet_class"
            :teachers="teachers"
            :section="section"
        />

        <SectionConfirmDialog
            v-model:open="archiveDialogOpen"
            action="archive"
            :section="section"
        />

        <SectionConfirmDialog
            v-model:open="deleteDialogOpen"
            action="delete"
            :section="section"
        />
    </div>
</template>

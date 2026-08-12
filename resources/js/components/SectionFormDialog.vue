<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    store,
    update,
} from '@/actions/App/Http/Controllers/SectionController';
import InputError from '@/components/InputError.vue';
import ReviewMasterMultiSelect from '@/components/ReviewMasterMultiSelect.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type SectionFormWorksheetClass = {
    id: number;
    name: string;
    slug: string;
};

export type SectionFormTeacher = {
    id: number;
    name: string;
    email: string;
};

export type SectionFormValues = {
    id: number;
    name: string;
    section_type: string;
    class_code: string;
    date_start: string;
    date_end: string;
    teacher_ids: number[];
};

const props = defineProps<{
    worksheetClass: SectionFormWorksheetClass;
    teachers: SectionFormTeacher[];
    section?: SectionFormValues | null;
}>();

const open = defineModel<boolean>('open', { default: false });

const isEditing = computed(() => props.section != null);
const selectedTeacherIds = ref<number[]>([]);
const fieldIdPrefix = computed(() =>
    isEditing.value ? `edit-section-${props.section?.id}` : 'create-section',
);

const classCodeExample = computed(() => {
    const yearMonth = isEditing.value
        ? props.section!.date_start.slice(0, 7).replace('-', '')
        : new Date().toISOString().slice(0, 7).replace('-', '');

    return `${yearMonth}-${props.worksheetClass.slug.toUpperCase()}-A`;
});

const formBindings = computed(() =>
    isEditing.value
        ? update.form(props.section!.id)
        : store.form(),
);

watch(
    open,
    (isOpen) => {
        if (!isOpen) {
            return;
        }

        selectedTeacherIds.value = props.section
            ? [...props.section.teacher_ids]
            : [];
    },
    { immediate: true },
);

function resetForm(reset: () => void, clearErrors: () => void): void {
    clearErrors();
    reset();
    selectedTeacherIds.value = props.section
        ? [...props.section.teacher_ids]
        : [];
}

function onSuccess(): void {
    open.value = false;
    selectedTeacherIds.value = props.section
        ? [...props.section.teacher_ids]
        : [];
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger v-if="$slots.default" as-child>
            <slot />
        </DialogTrigger>

        <DialogContent>
            <Form
                :key="
                    isEditing
                        ? `edit-${section?.id}-${open}`
                        : `create-${open}`
                "
                v-bind="formBindings"
                :reset-on-success="!isEditing"
                :options="isEditing ? { preserveScroll: true } : undefined"
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
                @success="onSuccess"
            >
                <DialogHeader>
                    <DialogTitle>
                        {{ isEditing ? 'Edit section' : 'Add section' }}
                    </DialogTitle>
                    <DialogDescription>
                        <template v-if="isEditing">
                            Update details for {{ section?.name }}.
                        </template>
                        <template v-else>
                            Create a new section for {{ worksheetClass.name }}.
                        </template>
                    </DialogDescription>
                </DialogHeader>

                <input
                    v-if="!isEditing"
                    type="hidden"
                    name="worksheet_class_id"
                    :value="worksheetClass.id"
                />

                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label :for="`${fieldIdPrefix}-name`">Name</Label>
                        <Input
                            :id="`${fieldIdPrefix}-name`"
                            name="name"
                            required
                            placeholder="Morning Batch A"
                            :default-value="section?.name"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label :for="`${fieldIdPrefix}-section_type`">
                            Section type
                        </Label>
                        <Input
                            :id="`${fieldIdPrefix}-section_type`"
                            name="section_type"
                            required
                            placeholder="Online, F2F, etc."
                            :default-value="section?.section_type"
                        />
                        <InputError :message="errors.section_type" />
                    </div>

                    <div class="grid gap-2">
                        <Label :for="`${fieldIdPrefix}-class_code`">
                            Class code
                        </Label>
                        <Input
                            :id="`${fieldIdPrefix}-class_code`"
                            name="class_code"
                            required
                            :placeholder="classCodeExample"
                            :default-value="section?.class_code"
                        />
                        <p class="text-xs text-muted-foreground">
                            Format: YYYYMM-{{
                                worksheetClass.slug.toUpperCase()
                            }}-A
                        </p>
                        <InputError :message="errors.class_code" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label :for="`${fieldIdPrefix}-date_start`">
                                Start date
                            </Label>
                            <Input
                                :id="`${fieldIdPrefix}-date_start`"
                                type="date"
                                name="date_start"
                                required
                                :default-value="section?.date_start"
                            />
                            <InputError :message="errors.date_start" />
                        </div>

                        <div class="grid gap-2">
                            <Label :for="`${fieldIdPrefix}-date_end`">
                                End date
                            </Label>
                            <Input
                                :id="`${fieldIdPrefix}-date_end`"
                                type="date"
                                name="date_end"
                                required
                                :default-value="section?.date_end"
                            />
                            <InputError :message="errors.date_end" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label>Review masters</Label>
                        <ReviewMasterMultiSelect
                            v-model="selectedTeacherIds"
                            :options="teachers"
                        />
                        <InputError :message="errors.teacher_ids" />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button
                            type="button"
                            variant="secondary"
                            @click="() => resetForm(reset, clearErrors)"
                        >
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        :disabled="processing"
                        :data-test="
                            isEditing
                                ? 'confirm-edit-section-button'
                                : 'confirm-create-section-button'
                        "
                    >
                        {{ isEditing ? 'Save changes' : 'Create section' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>

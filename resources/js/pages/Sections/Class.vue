<script setup lang="ts">
import { Form, Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { Plus, Users } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { store } from '@/actions/App/Http/Controllers/SectionController';
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
import { showClass as sectionClassRoute } from '@/routes/sections';

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

type TeacherItem = {
    id: number;
    name: string;
    email: string;
};

const props = defineProps<{
    worksheetClass: WorksheetClassItem;
    sections: SectionItem[];
    teachers: TeacherItem[];
}>();

const page = usePage();
const createDialogOpen = ref(false);
const selectedTeacherIds = ref<number[]>([]);

const isAdmin = computed(() => page.props.auth.user?.is_admin === true);

const classCodeExample = computed(
    () =>
        `${new Date().toISOString().slice(0, 7).replace('-', '')}-${props.worksheetClass.slug.toUpperCase()}-A`,
);

watch(createDialogOpen, (open) => {
    if (!open) {
        selectedTeacherIds.value = [];
    }
});

function resetCreateForm(
    reset: () => void,
    clearErrors: () => void,
): void {
    clearErrors();
    reset();
    selectedTeacherIds.value = [];
}

function onCreateSuccess(): void {
    createDialogOpen.value = false;
    selectedTeacherIds.value = [];
}

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Sections',
        },
        {
            title: props.worksheetClass.name,
            href: sectionClassRoute(props.worksheetClass.slug),
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
</script>

<template>
    <Head :title="worksheetClass.name" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold">{{ worksheetClass.name }}</h1>
                <p class="text-sm text-muted-foreground">
                    Sections for this class.
                </p>
            </div>

            <Dialog v-if="isAdmin" v-model:open="createDialogOpen">
                <DialogTrigger as-child>
                    <Button data-test="create-section-button">
                        <Plus class="size-4" />
                        Add section
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <Form
                        v-bind="store.form()"
                        reset-on-success
                        class="space-y-6"
                        v-slot="{ errors, processing, reset, clearErrors }"
                        @success="onCreateSuccess"
                    >
                        <DialogHeader>
                            <DialogTitle>Add section</DialogTitle>
                            <DialogDescription>
                                Create a new section for
                                {{ worksheetClass.name }}.
                            </DialogDescription>
                        </DialogHeader>

                        <input
                            type="hidden"
                            name="worksheet_class_id"
                            :value="worksheetClass.id"
                        />

                        <div class="grid gap-4">
                            <div class="grid gap-2">
                                <Label for="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    placeholder="Morning Batch A"
                                />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="class_code">Class code</Label>
                                <Input
                                    id="class_code"
                                    name="class_code"
                                    required
                                    :placeholder="classCodeExample"
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
                                    <Label for="date_start">Start date</Label>
                                    <Input
                                        id="date_start"
                                        type="date"
                                        name="date_start"
                                        required
                                    />
                                    <InputError :message="errors.date_start" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="date_end">End date</Label>
                                    <Input
                                        id="date_end"
                                        type="date"
                                        name="date_end"
                                        required
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
                                    @click="
                                        () =>
                                            resetCreateForm(reset, clearErrors)
                                    "
                                >
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button
                                type="submit"
                                :disabled="processing"
                                data-test="confirm-create-section-button"
                            >
                                Create section
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
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
                    Sections will appear here once they are available for this
                    class.
                </p>
            </div>
        </div>

        <div
            v-else
            class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <div
                class="grid grid-cols-[minmax(0,1fr)_11rem] gap-x-4 border-b border-sidebar-border/70 px-4 py-2 text-xs font-medium text-muted-foreground sm:grid-cols-[minmax(0,1fr)_12rem_7.5rem_7.5rem] dark:border-sidebar-border"
            >
                <span>Name</span>
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
                    class="grid grid-cols-[minmax(0,1fr)_11rem] gap-x-4 gap-y-1 px-4 py-3 text-sm sm:grid-cols-[minmax(0,1fr)_12rem_7.5rem_7.5rem]"
                >
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ section.name }}</p>
                        <p class="text-muted-foreground sm:hidden">
                            {{ formatDate(section.date_start) }} –
                            {{ formatDate(section.date_end) }}
                        </p>
                    </div>
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

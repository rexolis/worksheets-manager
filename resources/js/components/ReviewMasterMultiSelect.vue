<script setup lang="ts">
import { ChevronsUpDown, X } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export type ReviewMasterOption = {
    id: number;
    name: string;
    email: string;
};

const props = withDefaults(
    defineProps<{
        options: ReviewMasterOption[];
        name?: string;
        placeholder?: string;
        emptyText?: string;
        disabled?: boolean;
    }>(),
    {
        name: 'teacher_ids[]',
        placeholder: 'Select review masters',
        emptyText: 'No review masters available to assign.',
        disabled: false,
    },
);

const selectedIds = defineModel<number[]>({
    default: () => [],
});

const selectedMasters = computed(() =>
    props.options.filter((option) => selectedIds.value.includes(option.id)),
);

const triggerLabel = computed(() => {
    const count = selectedIds.value.length;

    if (count === 0) {
        return props.placeholder;
    }

    if (count === 1) {
        return selectedMasters.value[0]?.name ?? '1 selected';
    }

    return `${count} selected`;
});

function isSelected(id: number): boolean {
    return selectedIds.value.includes(id);
}

function setSelected(id: number, checked: boolean): void {
    if (checked) {
        if (!selectedIds.value.includes(id)) {
            selectedIds.value = [...selectedIds.value, id];
        }

        return;
    }

    selectedIds.value = selectedIds.value.filter(
        (selectedId) => selectedId !== id,
    );
}

function toggle(id: number): void {
    setSelected(id, !isSelected(id));
}

function remove(id: number): void {
    setSelected(id, false);
}
</script>

<template>
    <div class="grid gap-3">
        <input
            v-for="id in selectedIds"
            :key="id"
            type="hidden"
            :name="name"
            :value="id"
        />

        <p v-if="options.length === 0" class="text-sm text-muted-foreground">
            {{ emptyText }}
        </p>

        <DropdownMenu v-else>
            <DropdownMenuTrigger as-child>
                <Button
                    type="button"
                    variant="outline"
                    class="w-full justify-between font-normal"
                    :disabled="disabled"
                    data-test="review-master-multi-select-trigger"
                >
                    <span class="truncate">{{ triggerLabel }}</span>
                    <ChevronsUpDown class="size-4 shrink-0 opacity-50" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="min-w-64" align="start">
                <DropdownMenuItem
                    v-for="option in options"
                    :key="option.id"
                    class="cursor-pointer items-start gap-3 py-2"
                    @select.prevent="toggle(option.id)"
                >
                    <Checkbox
                        :model-value="isSelected(option.id)"
                        class="mt-0.5 pointer-events-none"
                        tabindex="-1"
                        aria-hidden="true"
                    />
                    <span class="min-w-0">
                        <span class="block font-medium">{{ option.name }}</span>
                        <span
                            class="block truncate text-xs text-muted-foreground"
                        >
                            {{ option.email }}
                        </span>
                    </span>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>

        <ul
            v-if="selectedMasters.length > 0"
            class="flex flex-wrap gap-2"
            data-test="review-master-selected-list"
        >
            <li
                v-for="master in selectedMasters"
                :key="master.id"
                class="inline-flex max-w-full items-center gap-1 rounded-md border border-border bg-muted/50 py-1 pr-1 pl-2 text-sm"
            >
                <span class="min-w-0 truncate">{{ master.name }}</span>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    class="size-6 shrink-0 text-muted-foreground hover:text-foreground"
                    :disabled="disabled"
                    :aria-label="`Remove ${master.name}`"
                    data-test="review-master-remove"
                    @click="remove(master.id)"
                >
                    <X class="size-3.5" />
                </Button>
            </li>
        </ul>
    </div>
</template>

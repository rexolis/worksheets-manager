<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { store } from '@/actions/App/Http/Controllers/StudentClassController';
import InputError from '@/components/InputError.vue';
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

const open = defineModel<boolean>('open', { default: false });

function resetForm(reset: () => void, clearErrors: () => void): void {
    clearErrors();
    reset();
}

function onSuccess(): void {
    open.value = false;
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger v-if="$slots.default" as-child>
            <slot />
        </DialogTrigger>

        <DialogContent>
            <Form
                :key="`enroll-${open}`"
                v-bind="store.form()"
                reset-on-success
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
                @success="onSuccess"
            >
                <DialogHeader>
                    <DialogTitle>Enroll in a class</DialogTitle>
                    <DialogDescription>
                        Enter the class code provided by your instructor to
                        request enrollment.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="enroll-class-code">Class code</Label>
                    <Input
                        id="enroll-class-code"
                        name="class_code"
                        required
                        autocomplete="off"
                        placeholder="202601-CSE-A"
                    />
                    <InputError :message="errors.class_code" />
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
                        data-test="confirm-enroll-class-button"
                    >
                        Enroll
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>

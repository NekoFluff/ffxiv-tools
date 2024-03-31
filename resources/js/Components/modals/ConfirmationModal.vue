<script setup lang="ts">
import { ref } from 'vue';
import Modal from '../Modal.vue';
import SecondaryButton from '../SecondaryButton.vue';
import DangerButton from '../DangerButton.vue';

defineProps<{
    title?: string;
    message: string;
    buttonText?: string;
}>();

const emit = defineEmits(['confirm']);

const show = ref(false);

const open = () => {
    show.value = true;
};

const close = () => {
    show.value = false;
};

defineExpose({ open, close });


</script>

<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ title ?? 'Are you sure?' }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ message }}
            </p>

            <div class="flex justify-end mt-6">
                <SecondaryButton @click="close"> Cancel </SecondaryButton>
                <DangerButton class="ms-3" @click="emit('confirm')">
                    {{ buttonText ?? 'YES' }}
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>

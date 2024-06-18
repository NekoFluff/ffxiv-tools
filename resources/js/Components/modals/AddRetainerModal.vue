<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "../PrimaryButton.vue";
import axios from "axios";
import TextInput from "../TextInput.vue";
import ServerDropdown from "../ServerDropdown.vue";

defineProps<{
    show?: boolean;
}>();

const form = useForm({
    name: "",
    server: "Goblin",
});

const emit = defineEmits(["close", "success"]);

const closeModal = () => {
    emit("close");
    form.clearErrors();
    form.reset();
};

const addRetainer = async () => {
    form.processing = true;

    axios
        .post(route("retainers.store"), {
            name: form.name,
            server: form.server,
        })
        .then((response) => {
            emit("success", response.data);
            closeModal();
        })
        .catch((error) => {
            for (const key in error.response.data.errors) {
                const formKey = key as keyof typeof form.data;
                const msg = error.response.data.errors[key][0] as string;
                form.setError(formKey, msg);
            }
        })
        .finally(() => {
            form.processing = false;
        });
};
</script>

<template>
    <Modal :show="show" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">Add a new retainer</h2>

            <p class="mt-1 text-sm text-gray-600">You have a limit of 10 retainers. Please enter the name and server of the new retainer.</p>

            <div class="mt-6">
                <InputLabel for="name" value="Name" class="sr-only" />

                <TextInput id="name" ref="nameInput" v-model="form.name" class="block w-3/4 mt-1" placeholder="Name" />

                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <div class="mt-6">
                <InputLabel for="server" value="Server" class="sr-only" />

                <!-- <TextInput id="server" ref="serverInput" v-model="form.server" class="block w-3/4 mt-1"
                    placeholder="Server" /> -->
                <ServerDropdown
                    class="block w-3/4 mt-1 text-lg"
                    :server="form.server"
                    @select="
                        (_server) => {
                            form.server = _server;
                        }
                    "
                />

                <InputError :message="form.errors.server" class="mt-2" />
            </div>

            <div class="flex justify-end mt-6">
                <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                <PrimaryButton class="ms-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="addRetainer">
                    Add Retainer
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import SearchBar from "../SearchBar.vue";
import PrimaryButton from "../PrimaryButton.vue";
import axios from "axios";
import { RetainerListingsSummary } from "@/types/retainer";
import SearchResult from "../SearchResult.vue";

const props = defineProps<{
    retainerID: number;
    retainerName: string;
    show?: boolean;
}>();

const form = useForm<{
    item_id: number | null;
}>({
    item_id: null,
});

const emit = defineEmits<{
    close: [];
    success: [summary: RetainerListingsSummary];
}>();

const searchBar = ref<InstanceType<typeof SearchBar> | null>(null);

const closeModal = () => {
    searchBar.value?.clear();
    emit("close");
    form.clearErrors();
    form.reset();
};

const addItem = async () => {
    if (form.processing) {
        return;
    }
    form.processing = true;
    form.clearErrors();

    axios
        .post(route("retainers.items.store", { retainerID: props.retainerID }), { item_id: form.item_id })
        .then((response) => {
            emit("success", response.data as RetainerListingsSummary);
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
            <h2 class="text-lg font-medium text-zinc-900">Add a new item to {{ props.retainerName }}</h2>

            <p class="mt-1 text-sm text-zinc-600">
                You have a limit of 20 items per retainer. Please choose the item you would like to add to this retainer.
            </p>

            <div class="mt-6">
                <InputLabel for="item" value="Item" class="sr-only" />
                <SearchBar ref="searchBar" id="item" class="flex-grow mr-6">
                    <template v-if="form.item_id" v-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="green" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </template>
                    <template v-slot:default="{ searchResult }">
                        <SearchResult
                            :search-result="searchResult"
                            @select="(_itemID: number, _itemName: string) => { form.item_id = _itemID; searchBar?.selectSearchResult(_itemName); }"
                        />
                    </template>
                </SearchBar>

                <InputError :message="form.errors.item_id" class="mt-2" />
            </div>

            <div class="flex justify-end mt-6">
                <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                <PrimaryButton class="ms-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="addItem">
                    Add Item
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>

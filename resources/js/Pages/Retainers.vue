<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import RetainerListingsSummaryTable from "@/Components/RetainerListingsSummaryTable.vue";
import AddRetainerModal from "@/Components/modals/AddRetainerModal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { RetainerListingsSummary, RetainerListingsSummaryItem } from "@/types/retainer";

import axios from "axios";
import { reactive } from "vue";
import { ref, onMounted } from "vue";

const retainerListingSummaries = ref<RetainerListingsSummary[]>([]);
const showModal = ref(false);
const retainersLoading = ref(false);

const getRetainers = async () => {
    if (retainersLoading.value) return;
    retainersLoading.value = true;

    try {
        const response = await axios.get("/api/retainers");

        for (const summary of response.data) {
            retainerListingSummaries.value.push(reactive(summary));
        }
    } catch (error) {
        // TODO: Handle error
        console.log("An unexpected error occurred", error);
    } finally {
        retainersLoading.value = false;
    }
};

const addRetainer = async () => {
    showModal.value = true;
};

const addRetainerListingSummary = (summary: RetainerListingsSummary) => {
    retainerListingSummaries.value.push(reactive(summary));
};

const deleteRetainerListingSummary = (retainerID: number) => {
    retainerListingSummaries.value = [...retainerListingSummaries.value.filter((summary) => summary.retainer_id !== retainerID)];
};

const addRetainerItem = (retainerID: number, item: RetainerListingsSummaryItem) => {
    retainerListingSummaries.value.find((summary) => summary.retainer_id === retainerID)?.items.push(item);

    retainerListingSummaries.value = [...retainerListingSummaries.value];
};

onMounted(() => {
    getRetainers();
});
</script>

<template>
    <!-- <Head title="Dashboard" /> -->

    <Head title="Retainers" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="mr-10 text-xl font-semibold leading-tight text-gray-800">Retainers</h2>
                <PrimaryButton @click="addRetainer">Add Retainer</PrimaryButton>
            </div>
        </template>

        <!-- <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">This feature has not been implemented yet</div>
                </div>
            </div>
        </div> -->

        <div class="py-10" v-if="!$page.props.auth.user">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">You must be logged in to use this feature</div>
                </div>
            </div>
        </div>

        <div v-if="retainersLoading && retainerListingSummaries.length === 0" class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">Loading Retainers...</div>
                </div>
            </div>
        </div>

        <div v-else v-for="summary in retainerListingSummaries" class="py-10">
            <div :key="summary.retainer_name" class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <RetainerListingsSummaryTable :summary="summary" @delete="deleteRetainerListingSummary" @new-retainer-item="addRetainerItem" />
            </div>
        </div>

        <AddRetainerModal :show="showModal" @close="showModal = false" @success="addRetainerListingSummary" />
    </AuthenticatedLayout>
</template>

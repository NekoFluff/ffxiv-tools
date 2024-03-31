<script setup lang="ts">
import { RetainerListingsSummary, RetainerListingsSummaryItem } from "@/types/retainer";
import { ref, watch } from "vue";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faTrashAlt, faPlus } from '@fortawesome/free-solid-svg-icons'
import AddRetainerItemModal from "./modals/AddRetainerItemModal.vue";
import axios from "axios";
import ConfirmationModal from "./modals/ConfirmationModal.vue";

type TableRow = {
    itemID: number;
    itemName: string;
    numListings: number;
    retainerListingPrice: number | null;
    lowestListingPrice: number | null;
    selected: boolean;
};

const props = defineProps<{
    summary: RetainerListingsSummary
}>();

const emit = defineEmits<{
    delete: [retainerId: number]
    newRetainerItem: [retainerId: number, item: RetainerListingsSummaryItem]
}>()

const showModal = ref(false);
const deleting = ref(false);
const confirmationModal = ref<InstanceType<typeof ConfirmationModal> | null>(null);

const tableRows = ref<TableRow[]>(props.summary.items.map(item => ({
    itemID: item.item_id,
    itemName: item.item_name,
    numListings: item.num_retainer_listings,
    retainerListingPrice: item.retainer_listing_price,
    lowestListingPrice: item.lowest_listing_price,
    selected: false
})));

watch(props.summary, (newSummary) => {
    tableRows.value = newSummary.items.map(item => ({
        itemID: item.item_id,
        itemName: item.item_name,
        numListings: item.num_retainer_listings,
        retainerListingPrice: item.retainer_listing_price,
        lowestListingPrice: item.lowest_listing_price,
        selected: false
    }));
});

const toggleAll = () => {
    const allItemsSelected = tableRows.value.every(row => row.selected);
    if (allItemsSelected) {
        tableRows.value.forEach(row => row.selected = false);
    } else {
        tableRows.value.forEach(row => row.selected = true);
    }
};

const addItem = () => {
    showModal.value = true;
};

const deleteRetainer = async () => {
    confirmationModal.value?.close();
    if (deleting.value) {
        return;
    }

    deleting.value = true;
    await axios.delete(route('retainers.destroy', { retainerID: props.summary.retainer_id })).then(() => {
        emit('delete', props.summary.retainer_id);
    }).finally(() => {
        deleting.value = false;
    });
}

const deleteSelectedItems = async () => {
    if (deleting.value) {
        return;
    }

    deleting.value = true;
    const selectedItems = tableRows.value.filter(row => row.selected);
    const itemIDs = selectedItems.map(item => item.itemID);

    const body = { item_ids: itemIDs };
    await axios.delete(route('retainers.items.destroy', { retainerID: props.summary.retainer_id }), { data: body }).then(() => {
        tableRows.value = tableRows.value.filter(row => !row.selected);
    }).finally(() => {
        deleting.value = false;
    })
};
</script>

<style>
.slide-move .slide-enter-active .slide-leave-active {
    transition: all 0.5s ease-in-out;
}

.slide-enter-from,
.slide-leave-to {
    opacity: 0;
    transform: translateX(-100%);
}

.slide-enter-active {
    opacity: 1;
    transform: translateX(0);
}

.slide-leave-active {
    position: absolute;
}
</style>

<template>
    <div class="text-black bg-white rounded-lg">
        <div class="flex items-center justify-between px-6 py-5">
            <span class="flex">
                <h1 class="text-2xl font-bold text-center">{{ summary.retainer_name }}</h1>
                <p class="text-sm text-center">
                    &nbsp;[{{ summary.server }}]
                </p>
                <button class="ml-3" @click="confirmationModal?.open()">
                    <FontAwesomeIcon :icon="faTrashAlt" />
                </button>
            </span>
            <button v-if="!tableRows.some(row => row.selected)" @click="addItem">
                <FontAwesomeIcon :icon="faPlus" />
            </button>
            <button v-else @click="deleteSelectedItems" :disabled="deleting">
                <FontAwesomeIcon :icon="faTrashAlt" />
            </button>
        </div>
        <table class="table w-full border-collapse rounded">
            <thead>
                <tr>
                    <th class="w-6 pl-6 text-center">
                        <input type="checkbox" class="w-5 h-5 border-2 border-black rounded-sm" @click="toggleAll"
                            :checked="tableRows.every(row => row.selected)" />
                    </th>
                    <th class="px-6 py-3 text-left">Item Name</th>
                    <th class="px-6 py-3 text-right"># Listings</th>
                    <th class="px-6 py-3 text-right">Retainer Listing Price</th>
                    <th class="px-6 py-3 text-right">Lowest Listing Price</th>
                </tr>
            </thead>

            <tbody name="slide" is="transition-group">
                <tr v-for="tableRow in tableRows" :key="tableRow.itemID" :class="{
                    'bg-green-500': tableRow.retainerListingPrice && tableRow.lowestListingPrice && (tableRow.retainerListingPrice <= tableRow.lowestListingPrice),
                    'bg-red-500': tableRow.retainerListingPrice && tableRow.lowestListingPrice && (tableRow.retainerListingPrice > tableRow.lowestListingPrice),
                    'bg-yellow-500': tableRow.retainerListingPrice === null
                }">
                    <td class="pl-6 text-center">
                        <input type="checkbox" class="w-5 h-5 border-2 border-black rounded-sm"
                            v-model="tableRow.selected" />
                    </td>
                    <td class="px-6 py-3 font-semibold">{{ tableRow.itemName }}</td>
                    <td class="px-6 py-3 text-right">{{ tableRow.numListings }}</td>
                    <td class="px-6 py-3 text-right">{{ tableRow.retainerListingPrice ?? 'No Listings' }}</td>
                    <td class="px-6 py-3 text-right">{{ tableRow.lowestListingPrice ?? 'No Listings' }}</td>
                </tr>
            </tbody>
        </table>

        <AddRetainerItemModal v-if="$page.props.auth.user" :show="showModal" :retainer-i-d="summary.retainer_id"
            :retainer-name="summary.retainer_name" @close="() => showModal = false"
            @success="(summary: RetainerListingsSummary) => { emit('newRetainerItem', summary.retainer_id, summary.items[0]) }" />

        <ConfirmationModal ref="confirmationModal"
            message="There is no going back from this action. This will delete the retainer and all of its items."
            button-text="Delete Retainer" @confirm="deleteRetainer" />

    </div>
</template>

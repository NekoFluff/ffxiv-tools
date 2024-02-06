<script setup lang="ts">

defineProps<{
    listings: Array<{}>
}>();

</script>


<template>
    <table class="table w-full border border-collapse">
        <thead>
            <tr>
                <th class="px-4 py-2 bg-white border-b">Price</th>
                <th class="px-4 py-2 bg-white border-b">Amount</th>
                <th class="px-4 py-2 bg-white border-b">Retainer</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="listing in listings" :key="listing['listingID']" class="border-b">
                <td class="px-4 py-2">{{ listing['pricePerUnit'] }}</td>
                <td class="px-4 py-2">{{ listing['quantity'] }}</td>
                <td class="px-4 py-2">{{ listing['retainerName'] }}</td>
            </tr>
        </tbody>
    </table>
</template>

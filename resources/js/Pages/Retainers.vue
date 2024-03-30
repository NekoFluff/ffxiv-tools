<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import axios from 'axios';
import { onMounted } from 'vue';

const getRetainers = async () => {
    try {
        const response = await axios.get('/api/retainers');
        console.log("RETAINERS RESPONSE", response.data)
        // Process the response data here
    } catch (error) {
        console.log("ERROR", error)
        // Handle the error here
    }
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
            </div>
        </template>




        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">This feature has not been implemented yet</div>
                </div>
            </div>
        </div>
        <!-- <div v-if="!$page.props.auth.user">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">You must be logged in to use this feature</div>
                </div>
            </div>
        </div> -->
    </AuthenticatedLayout>
</template>

<script setup>
import Searchbar from '@/Components/SearchBar.vue';
import Nav from '@/Components/Nav.vue';

import { Link, router } from "@inertiajs/vue3"
const props = defineProps({
    username: {
        type: String,
        required: true
    }
})

</script>

<template>
    Home sweet home
</template>
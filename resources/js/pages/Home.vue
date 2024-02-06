<script setup>
import Searchbar from '../components/searchbar.vue';
import Nav from '../components/Nav.vue';

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
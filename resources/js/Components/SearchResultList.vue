<script setup lang="ts">
import SearchResult from "./SearchResult.vue";
import type { Option } from "./SearchBar.vue";

defineProps<{
    options: Option[];
}>();

</script>


<template>
    <div class="relative z-10 bg-blue-500" v-show="options.length > 0">
        <div class="absolute w-full bg-blue-500">
            <ul data-cy="searchResults">
                <SearchResult v-for="option of options" :text="option.text" :id="option.id">
                    <template #icon>
                        <img v-if="option.image" class="inline w-6 h-6" :src="option.image" :alt="option.text" />
                    </template>
                </SearchResult>
            </ul>
        </div>
    </div>
</template>
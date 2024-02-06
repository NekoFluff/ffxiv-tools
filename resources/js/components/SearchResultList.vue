<script setup lang="ts">
import SearchResult from "./SearchResult.vue";
import type { Option } from "./SearchBar.vue";

defineProps<{
    options: Option[];
}>();

defineEmits(['searchResultClick'])

</script>


<template>
    <div class="absolute z-10 bg-blue-500" v-show="options.length > 0">
        <ul data-cy="searchResults">
            <SearchResult v-for="option of options" :text="option.text" :id="option.id"
                @click="(text: string) => { $emit('searchResultClick', text) }">
                <template #icon>
                    <img v-if="option.image" class="inline w-6 h-6" :src="option.image" :alt="option.text" />
                </template>
            </SearchResult>
        </ul>
    </div>
</template>
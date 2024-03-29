<script setup lang="ts">
import SearchResult from "./SearchResult.vue";
import type { Option } from "./SearchBar.vue";

defineProps<{
    options: Option[];
}>();

</script>

<style>
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background-color: transparent;
}

::-webkit-scrollbar-thumb {
    background-color: #1533b6;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background-color: #a0a0a0;
}
</style>

<template>
    <div class="relative z-10 " v-show="options.length > 0">
        <div class="absolute w-full overflow-auto bg-blue-500 max-h-96 scrollbar">
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
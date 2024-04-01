<script setup lang="ts">
import { ref, defineExpose } from "vue"
import { debounce } from "lodash"
import axios from "axios";

export type SearchResult = {
    text: string;
    image: string | undefined;
    id: number;
};

const emits = defineEmits(["search"])

let text = ref<string>("")
let searchResults = ref<SearchResult[]>([])
let searchResultsVisible = ref<boolean>(true)

let search = debounce(() => {
    axios.get(`https://xivapi.com/search?indexes=Item&string=${text.value}`).then((response) => {
        searchResults.value = response.data.Results.filter(
            (result: any) => result.UrlType === "Item"
        ).map((result: any) => {
            return {
                text: result.Name,
                image: "https://xivapi.com/" + result.Icon,
                id: result.ID
            }
        })
    })
}, 500)

const handleUpdate = (updatedText: string) => {
    search()
    showSearchResults()
    text.value = updatedText
}

const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Enter') {
        // emit('search', text.value)
    }
}

const hideSearchResults = () => {
    searchResultsVisible.value = false
}

const showSearchResults = () => {
    searchResultsVisible.value = true
}

const clear = () => {
    text.value = ""
    searchResults.value = []
};

const selectSearchResult = (searchResultName: string) => {
    searchResultsVisible.value = false;
    text.value = searchResultName;
}

defineExpose({ clear, selectSearchResult });

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
    <div v-click-outside="hideSearchResults">
        <input :value="text"
            class="w-full p-2 text-black bg-white border-gray-300 rounded-md shadow-md outline-none placeholder-slate-700 shadow-grey-900"
            type="text" placeholder="Search..." @input="handleUpdate(($event.target as HTMLInputElement).value)"
            @keydown="handleKeyDown" @focus="showSearchResults" autocomplete="off" />

        <div v-if="searchResultsVisible" class="mt-0">
            <div class="relative z-10" v-show="searchResults.length > 0">
                <div class="absolute w-full overflow-auto bg-blue-500 max-h-96 scrollbar">
                    <ul>
                        <slot v-for="searchResult in searchResults" :searchResult="searchResult" />
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

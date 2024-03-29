<script setup lang="ts">
import { ref } from "vue"
import { debounce } from "lodash"
import axios from "axios";
import SearchResultList from "./SearchResultList.vue";

export type Option = {
    text: string;
    image: string | undefined;
    id: number;
};

const emit = defineEmits(["search"])

let text = ref<string>("")
let options = ref<Option[]>([])
let optionsVisible = ref<boolean>(true)

let search = debounce(() => {
    axios.get(`https://xivapi.com/search?indexes=Item&string=${text.value}`).then((response) => {
        options.value = response.data.Results.filter(
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
    showOptions()
    text.value = updatedText
}

const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Enter') {
        // emit('search', text.value)
    }
}

const hideOptions = () => {
    optionsVisible.value = false
}

const showOptions = () => {
    optionsVisible.value = true
}

const vClickOutside = {
    beforeMount(el: any, binding: any) {
        el.clickOutsideEvent = function (event: Event) {
            if (!(el == event.target || el.contains(event.target as Node))) {
                binding.value()
            }
        }
        document.body.addEventListener('click', el.clickOutsideEvent)
    },
    unmounted(el: any) {
        document.body.removeEventListener('click', el.clickOutsideEvent)
    }
}

</script>

<template>
    <div v-click-outside="hideOptions" data-cy="searchBar">
        <input :value="text"
            class="w-full p-2 text-black bg-white border-gray-300 rounded-md shadow-md outline-none placeholder-slate-700 shadow-grey-900"
            type="text" placeholder="Search..." @input="handleUpdate(($event.target as HTMLInputElement).value)"
            @keydown="handleKeyDown" @focus="showOptions" autocomplete="off" />
        <SearchResultList v-if="optionsVisible" class="mt-0" :options="options" />
    </div>
</template>

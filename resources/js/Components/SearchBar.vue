<script setup lang="ts">
import { ref, defineExpose } from "vue"
import { debounce } from "lodash"
import axios from "axios";
import SearchResultList from "./SearchResultList.vue";

export type Option = {
    text: string;
    image: string | undefined;
    id: number;
};

const emits = defineEmits(["search", "select"])

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

const clear = () => {
    text.value = ""
    options.value = []
};

defineExpose({ clear });

</script>

<template>
    <div v-click-outside="hideOptions">
        <input :value="text"
            class="w-full p-2 text-black bg-white border-gray-300 rounded-md shadow-md outline-none placeholder-slate-700 shadow-grey-900"
            type="text" placeholder="Search..." @input="handleUpdate(($event.target as HTMLInputElement).value)"
            @keydown="handleKeyDown" @focus="showOptions" autocomplete="off" />
        <SearchResultList v-if="optionsVisible" class="mt-0" :options="options"
            @select="(optionID, optionName) => { emits('select', optionID, optionName); optionsVisible = false; text = optionName }" />
    </div>
</template>

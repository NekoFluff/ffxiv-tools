<script setup lang="ts">
import { ref, defineEmits, defineProps } from "vue"

const emits = defineEmits(["select"])
defineProps<{
    server: string
}>()

const open = ref(false);
const servers = ['Adamantoise', 'Aegis', 'Alexander', 'Anima', 'Asura', 'Atomos', 'Bahamut', 'Balmung', 'Behemoth', 'Belias', 'Brynhildr', 'Cactuar', 'Carbuncle', 'Cerberus', 'Chocobo', 'Coeurl', 'Diabolos', 'Durandal', 'Excalibur', 'Exodus', 'Famfrit', 'Fenrir', 'Garuda', 'Gilgamesh', 'Goblin', 'Gungnir', 'Hades', 'Hyperion', 'Ifrit', 'Ixion', 'Jenova', 'Kujata', 'Lamia', 'Leviathan', 'Lich', 'Louisoix', 'Malboro', 'Mandragora', 'Masamune', 'Mateus', 'Midgardsormr', 'Moogle', 'Odin', 'Omega', 'Pandaemonium', 'Phoenix', 'Ragnarok', 'Ramuh', 'Ridill', 'Sargatanas', 'Shinryu', 'Shiva', 'Siren', 'Tiamat', 'Titan', 'Tonberry', 'Typhon', 'Ultima', 'Ultros', 'Unicorn', 'Valefor', 'Yojimbo', 'Zalera', 'Zeromus', 'Zodiark'];

const closeDropdown = () => {
    open.value = false;
}

</script>

<template>
    <div class="relative" v-click-outside="closeDropdown">
        <button @click="open = !open"
            class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium text-left text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm cursor-pointer min-w-40 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <span>{{ server }}&nbsp;</span>
            <!-- Heroicon name: chevron-down -->
            <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                aria-hidden="true">
                <path fill-rule="evenodd" d="M10 12l-6-6 1.5-1.5L10 9l4.5-4.5L16 6z" />
            </svg>
        </button>

        <div v-show="open"
            class="absolute z-10 w-full mt-2 overflow-scroll bg-white border border-gray-300 rounded-md shadow-lg max-h-96">
            <ul role="listbox" aria-labelledby="listbox-label" aria-activedescendant="listbox-option-3"
                class="py-1 overflow-auto text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                <li v-for="server in servers" :key="server"
                    class="relative py-2 pl-3 text-gray-900 cursor-pointer select-none pr-9"
                    @click="open = false; emits('select', server);">
                    <span class="block truncate">
                        {{ server }}
                    </span>
                </li>
            </ul>
        </div>
    </div>
</template>

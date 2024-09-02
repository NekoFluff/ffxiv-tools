<div>
    <div x-data="{ open: false }" x-on:retainer-created.window="open = ! open">
        <button class="flex justify-center px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
            @click="open = ! open">Create Retainer</button>

        @teleport('body')
            <div x-show="open" scroll-region>
                <div class="fixed inset-0" @click="open = ! open">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" />
                </div>
                <div class="absolute w-10/12 mb-6 transition-all transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl top-1/2 left-1/2 md:w-5/12 lg:w-4/12"
                    @click.stop>

                    <form wire:submit="save">
                        <div class="p-4">
                            {{-- Name Input --}}
                            <label for="name" class="block mb-1 font-bold text-gray-700">Name</label>
                            <input type="text" id="name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-500"
                                placeholder="Enter name" wire:model="name">
                            <div class="mt-1">
                                @error('name')
                                    <span class="text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Server Dropdown --}}
                            <label for="server" class="block mt-4 mb-1 font-bold text-gray-700">Server</label>
                            <select id="server"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-500"
                                wire:model="server">
                                @foreach ($this->servers() as $server)
                                    <option wire:key="{{ $server }}" value="{{ $server }}">{{ $server }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-1 mb-4">
                                @error('server')
                                    <span class="text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit"
                                class="flex justify-center px-4 py-2 text-white bg-blue-500 rounded-md hover:bg-blue-600"
                                wire:loading.class="opacity-75 disabled">

                                Submit
                                <div wire:loading class="ml-3">
                                    <svg class="w-5 h-5 text-black animate-spin dark:text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endteleport
    </div>
</div>

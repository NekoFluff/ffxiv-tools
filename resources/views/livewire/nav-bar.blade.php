<div>
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                {{-- Logo --}}
                <div class="flex items-center shrink-0">
                    {{-- <Link :href="route('dashboard')">
                        <ApplicationLogo class="block w-auto text-gray-800 fill-current h-9" />
                    </Link> --}}
                </div>

                {{-- Navigation Links --}}
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    {{-- <NavLink :href="route('dashboard')" :active="route().current('dashboard')"> Dashboard </NavLink> --}}
                    {{-- <NavLink :href="route('retainers')" :active="route().current('retainers')"> Retainers </NavLink> --}}
                </div>
            </div>

            <div v-if="$page.props.auth.user" class="hidden sm:flex sm:items-center sm:ms-6">
                {{-- Settings Dropdown --}}
                <div class="relative ms-3">
                    <Dropdown align="right" width="48">
                        <span class="inline-flex rounded-md">
                            <button type="button"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none">
                                {{ Auth::user()?->name ?? 'Username' }}

                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </span>

                        <DropdownLink href="{{ route('profile.edit') }}"> Profile </DropdownLink>
                        <DropdownLink href="{{ route('logout') }}" method="post" as="button"> Log Out
                    </Dropdown>
                </div>
            </div>

            <div v-else class="flex items-center">
                <a class="mr-3" href="{{ route('login') }}" wire:navigate> Log in </a>

                <a href="{{ route('register') }}" wire:navigate> Register </a>
            </div>

            {{-- Hamburger --}}
            {{-- <div v-if="$page.props.auth.user" class="flex items-center -me-2 sm:hidden">
                <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                    class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path
                            :class="{
                                hidden: showingNavigationDropdown,
                                'inline-flex': !showingNavigationDropdown,
                            }"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path
                            :class="{
                                hidden: !showingNavigationDropdown,
                                'inline-flex': showingNavigationDropdown,
                            }"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div> --}}
        </div>
    </div>
</div>

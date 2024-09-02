<div x-data="darkModeToggle()" x-init="initialize()"
    @click="function () { toggleDarkMode(); $wire.dispatch('dark-mode-toggled', this.isDarkMode); } "
    class="cursor-pointer">
    <svg x-show="!isDarkMode" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path class="text-gray-600" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 3v1m0 16v1m8.66-8.66h-1M4.34 12h-1m15.36 4.95l-.7-.7M6.34 6.34l-.7-.7m12.02 12.02l-.7-.7M6.34 17.66l-.7-.7M12 5a7 7 0 100 14 7 7 0 000-14z" />
    </svg>
    <svg x-show="isDarkMode" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path class="text-gray-400" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
    </svg>
</div>

<script>
    function darkModeToggle() {
        return {
            isDarkMode: false,
            initialize() {
                this.isDarkMode = localStorage.getItem('darkMode') === 'true';
                this.applyDarkMode();
            },
            toggleDarkMode() {
                this.isDarkMode = !this.isDarkMode;
                localStorage.setItem('darkMode', this.isDarkMode);
                this.applyDarkMode();
            },
            applyDarkMode() {
                if (this.isDarkMode) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        }
    }
</script>

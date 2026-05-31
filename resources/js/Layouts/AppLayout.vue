<template>
  <div>
    <nav class="bg-white dark:bg-zinc-800 shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-4 h-16">
        <Link :href="route('dashboard')" class="text-xl font-bold text-zinc-900 dark:text-white shrink-0">FFXIV Tools</Link>
        <div class="flex-1 max-w-sm">
          <ItemSearchBar />
        </div>
        <div class="flex items-center gap-4 ml-auto">
          <template v-if="$page.props.auth?.user">
            <Link :href="route('retainers')" class="text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white text-sm">Retainers</Link>
            <Link :href="route('profile.edit')" class="text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white text-sm">Profile</Link>
            <button @click="logout" class="text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white text-sm">Logout</button>
          </template>
          <template v-else>
            <Link :href="route('login')" class="text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white text-sm">Login</Link>
            <Link :href="route('register')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Register</Link>
          </template>
        </div>
      </div>
    </nav>
    <main>
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import ItemSearchBar from '@/Components/ItemSearchBar.vue';

function logout() {
  router.post(route('logout'));
}
</script>

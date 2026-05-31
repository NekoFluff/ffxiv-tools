<template>
  <div>
    <nav class="bg-white dark:bg-zinc-800 shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <Link :href="route('dashboard')" class="text-xl font-bold text-zinc-900 dark:text-white">FFXIV Tools</Link>
        <div class="flex items-center gap-4">
          <Link :href="route('retainers')" class="text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white text-sm">Retainers</Link>
          <Link :href="route('profile.edit')" class="text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white text-sm">Profile</Link>
          <button @click="logout" class="text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white text-sm">Logout</button>
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

function logout() {
  router.post(route('logout'));
}
</script>

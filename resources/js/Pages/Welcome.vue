<template>
  <div class="flex flex-col items-center justify-center min-h-screen px-4 bg-zinc-100 dark:bg-zinc-900">
    <h1 class="mb-4 text-4xl font-bold text-zinc-900 dark:text-white">FFXIV Tools</h1>
    <p class="max-w-md mb-8 text-center text-zinc-600 dark:text-zinc-400">
      Check how profitable it is to craft items on the FFXIV market.
    </p>
    <div class="w-full max-w-lg mb-8">
      <ItemSearchBar />
    </div>
    <div class="flex gap-4">
      <Link :href="route('login')" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Login</Link>
      <Link :href="route('register')" class="px-5 py-2.5 bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-white rounded-lg text-sm font-medium">Register</Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ItemSearchBar from '@/Components/ItemSearchBar.vue';
</script>

<template>
  <div class="min-h-screen bg-zinc-100 dark:bg-zinc-900 flex flex-col items-center justify-center px-4">
    <h1 class="text-4xl font-bold text-zinc-900 dark:text-white mb-4">FFXIV Tools</h1>
    <p class="text-zinc-600 dark:text-zinc-400 mb-8 text-center max-w-md">
      Check how profitable it is to craft items on the FFXIV market.
    </p>
    <div class="flex gap-4">
      <Link :href="route('login')" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Login</Link>
      <Link :href="route('register')" class="px-5 py-2.5 bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-white rounded-lg text-sm font-medium">Register</Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

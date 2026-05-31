<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-zinc-100 dark:bg-zinc-900 py-12">
    <Link :href="route('welcome')" class="mb-8 text-2xl font-bold text-zinc-900 dark:text-white">FFXIV Tools</Link>
    <div class="w-full sm:max-w-md bg-white dark:bg-zinc-800 shadow-md rounded-lg p-6">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

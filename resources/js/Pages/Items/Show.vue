<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ item?.name ?? 'Item Not Found' }}</h1>
          </div>
          <ServerDropdown :servers="servers" :current-server="server" @server-changed="onServerChanged" />
        </div>

        <template v-if="item">
          <!-- Current Listings -->
          <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-4">Current Listings</h2>
            <table class="w-full text-sm text-left text-zinc-700 dark:text-zinc-300">
              <thead class="text-xs text-zinc-500 dark:text-zinc-400 uppercase">
                <tr>
                  <th class="pb-2">Price per unit</th>
                  <th class="pb-2">Quantity</th>
                  <th class="pb-2">Total</th>
                  <th class="pb-2">Retainer</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="listing in listings" :key="listing.id" class="border-t border-zinc-100 dark:border-zinc-700">
                  <td class="py-1.5">{{ listing.price_per_unit.toLocaleString() }} gil</td>
                  <td class="py-1.5">{{ listing.quantity }}</td>
                  <td class="py-1.5">{{ listing.total.toLocaleString() }} gil</td>
                  <td class="py-1.5">{{ listing.retainer_name }}</td>
                </tr>
                <tr v-if="!listings.length">
                  <td colspan="4" class="py-3 text-zinc-400 dark:text-zinc-500">No listings found.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Crafting info -->
          <div v-if="craftableItem" class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-4">Crafting Analysis</h2>
            <CraftableItemBox :item="craftableItem" />
          </div>

          <div v-else class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 mb-6 text-zinc-500 dark:text-zinc-400">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-1">Crafting Analysis</h2>
            <p class="text-sm">{{ item?.name }} cannot be crafted.</p>
          </div>

          <!-- Price History Chart -->
          <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-4">Price History (7 days)</h2>
            <canvas ref="priceChartRef" height="80"></canvas>
          </div>

          <!-- Quantity Sold Chart -->
          <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-4">Quantity Sold (7 days)</h2>
            <canvas ref="qtyChartRef" height="80"></canvas>
          </div>
        </template>

        <div v-else class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 text-zinc-500 dark:text-zinc-400">
          Item not found.
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ServerDropdown from '@/Components/ServerDropdown.vue';
import CraftableItemBox from '@/Components/CraftableItemBox.vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
  item: Object,
  server: String,
  servers: Array,
  craftableItem: Object,
  listings: Array,
  priceHistory: Object,
  quantitySold: Object,
});

const priceChartRef = ref(null);
const qtyChartRef = ref(null);

function onServerChanged(server) {
  router.get(route('item.show', props.item.id), { server }, { preserveState: false });
}

onMounted(() => {
  if (priceChartRef.value && props.priceHistory) {
    const labels = Object.keys(props.priceHistory.average ?? {});
    new Chart(priceChartRef.value, {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label: 'Average', data: Object.values(props.priceHistory.average ?? {}), borderColor: '#6366f1', tension: 0.3, fill: false },
          { label: 'Min', data: Object.values(props.priceHistory.min ?? {}), borderColor: '#22c55e', tension: 0.3, fill: false },
          { label: 'Max', data: Object.values(props.priceHistory.max ?? {}), borderColor: '#ef4444', tension: 0.3, fill: false },
        ],
      },
      options: { plugins: { legend: { position: 'top' } } },
    });
  }

  if (qtyChartRef.value && props.quantitySold) {
    const labels = Object.keys(props.quantitySold);
    new Chart(qtyChartRef.value, {
      type: 'bar',
      data: {
        labels,
        datasets: [{ label: 'Quantity Sold', data: Object.values(props.quantitySold), backgroundColor: '#6366f1' }],
      },
    });
  }
});
</script>

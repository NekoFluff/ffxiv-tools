<template>
  <section class="py-3 pl-3 pr-1 mt-2 ml-5 border border-dashed rounded shadow-lg border-slate-500 dark:border-slate-100">
    <!-- Icon, name, output count, badges -->
    <div class="flex flex-wrap items-center gap-2">
      <img v-if="item.icon" :src="'https://v2.xivapi.com/api/asset?format=png&path=' + item.icon" class="w-6 h-6 rounded" />
      <a :href="route('item.show', item.item_id)" class="text-sm font-bold dark:text-white hover:underline">
        {{ item.name }}
      </a>
      <span class="text-sm text-zinc-600 dark:text-zinc-300">(x{{ item.crafting_output_count }})</span>

      <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
        Market: {{ item.market_price.toLocaleString() }} gil
      </span>
      <span v-if="item.vendor_price > 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
        Vendor: {{ item.vendor_price.toLocaleString() }} gil
      </span>
      <span v-if="item.market_price_updated_at" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200">
        Updated {{ updatedAgo }}
      </span>
      <template v-if="item.class_job_level > 0">
        <span :class="jobBadgeClass" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
          {{ item.class_job }}
        </span>
        <span :class="levelBadgeClass" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
          Lv {{ item.class_job_level }}
        </span>
      </template>
    </div>

    <!-- Cost breakdown + profit -->
    <div v-if="item.optimal_craft_cost > 0" class="flex gap-3 mt-2">
      <!-- Costs -->
      <div class="flex-1 p-2 space-y-1 text-xs font-medium border rounded dark:border-slate-600">
        <div :class="costColor('purchase')">
          Purchase Cost: {{ item.purchase_cost.toLocaleString() }} gil
          <span v-if="item.crafting_output_count > 1"> ({{ perUnit(item.purchase_cost) }} ea.)</span>
        </div>
        <div :class="costColor('market')">
          Market Craft Cost: {{ item.market_craft_cost.toLocaleString() }} gil
          <span v-if="item.crafting_output_count > 1"> ({{ perUnit(item.market_craft_cost) }} ea.)</span>
        </div>
        <div :class="costColor('optimal')">
          Optimal Craft Cost: {{ item.optimal_craft_cost.toLocaleString() }} gil
          <span v-if="item.crafting_output_count > 1"> ({{ perUnit(item.optimal_craft_cost) }} ea.)</span>
        </div>
      </div>

      <!-- Profit -->
      <div class="flex flex-col justify-center flex-1 p-2 space-y-2 text-xs font-medium border rounded dark:border-slate-600 dark:text-white">
        <div>
          Profit if Crafted:
          <span :class="profit >= 0 ? 'text-green-500' : 'text-red-500'">
            {{ profit.toLocaleString() }} gil
          </span>
        </div>
        <div>
          Profit Ratio:
          <span :class="profit >= 0 ? 'text-green-500' : 'text-red-500'">
            {{ profitRatio }}
          </span>
        </div>
      </div>
    </div>

    <!-- Recursive sub-materials -->
    <CraftableItemBox
      v-for="mat in item.crafting_materials"
      :key="mat.item_id + '.' + item.name"
      :item="mat"
    />
  </section>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  item: Object,
});

const updatedAgo = computed(() => {
  if (!props.item.market_price_updated_at) return null;
  const seconds = Math.floor(Date.now() / 1000) - props.item.market_price_updated_at;
  if (seconds < 60) return `${seconds}s ago`;
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
  return `${Math.floor(seconds / 86400)}d ago`;
});

const profit = computed(() => {
  return props.item.market_price * props.item.crafting_output_count - props.item.optimal_craft_cost;
});

const profitRatio = computed(() => {
  if (!props.item.optimal_craft_cost) return 'N/A';
  const ratio = ((props.item.market_price * props.item.crafting_output_count) / props.item.optimal_craft_cost) * 100 - 100;
  return ratio.toFixed(2) + '%';
});

function perUnit(cost) {
  return Math.round(cost / props.item.crafting_output_count).toLocaleString();
}

// Highlight cheapest cost option in green, others slate
const costColor = computed(() => (type) => {
  const costs = {
    purchase: props.item.purchase_cost,
    market: props.item.market_craft_cost,
    optimal: props.item.optimal_craft_cost,
  };
  const minVal = Math.min(...Object.values(costs).filter(v => v > 0));
  const val = costs[type];
  if (val <= 0) return 'text-zinc-500 dark:text-zinc-400';
  return val === minVal ? 'text-green-600 dark:text-green-400' : 'text-zinc-700 dark:text-zinc-300';
});

const JOB_COLORS = {
  Alchemist: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
  Armorer: 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200',
  Blacksmith: 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200',
  Carpenter: 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
  Culinarian: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
  Goldsmith: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
  Leatherworker: 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
  Weaver: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
  Botanist: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
  Fisher: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
  Miner: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
};

const jobBadgeClass = computed(() =>
  JOB_COLORS[props.item.class_job] ?? 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200'
);

const levelBadgeClass = computed(() => {
  const lv = props.item.class_job_level;
  if (lv <= 0) return 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200';
  if (lv <= 50) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
  if (lv <= 60) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
  if (lv <= 70) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
  if (lv <= 80) return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200';
  if (lv <= 90) return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
  return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
});
</script>

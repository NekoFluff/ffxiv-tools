<script setup lang="ts">
import { Line } from "vue-chartjs";
// import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
// ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip } from "chart.js";

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip);

const props = defineProps<{
    history: Array<{}>;
}>();

const options = {
    plugins: {
        legend: {
            display: false,
        },
    },
    responsive: true,
    interaction: {
        mode: "index" as "index",
        intersect: false,
    },
    scales: {
        y: {
            min: 0,
        },
    },
};

const quantityGraphOptions = {
    ...options,
    plugins: {
        title: {
            display: true,
            text: "Quantity Sold",
            font: {
                size: 20,
            },
        },
    },
};

const priceGraphOptions = {
    ...options,
    plugins: {
        title: {
            display: true,
            text: "Price History",
            font: {
                size: 20,
            },
        },
    },
};

const quantityData = {
    labels: props.history.map((item: any) => item.date),
    datasets: [
        {
            label: "# Sold",
            data: props.history.map((item: any) => item.quantity),
            fill: false,
            borderColor: "#c319ee",
            backgroundColor: "#c319ee",
            tension: 0.3,
            pointRadius: 0,
        },
    ],
};

const priceData = {
    labels: props.history.map((item: any) => item.date),
    datasets: [
        {
            label: "Avg Price",
            data: props.history.map((item: any) => item.avg_price),
            fill: false,
            borderColor: "#ffd700",
            backgroundColor: "#ffd700",
            tension: 0.3,
            pointRadius: 0,
        },
        {
            label: "Median Price",
            data: props.history.map((item: any) => item.median_price),
            fill: false,
            borderColor: "orange",
            backgroundColor: "orange",
            tension: 0.3,
            pointRadius: 0,
        },
        {
            label: "Min Price",
            data: props.history.map((item: any) => item.min_price),
            fill: false,
            borderColor: "#80ff80",
            backgroundColor: "#80ff80",
            tension: 0.3,
            pointRadius: 0,
        },
        {
            label: "Max Price",
            data: props.history.map((item: any) => item.max_price),
            fill: false,
            borderColor: "#ff0000",
            backgroundColor: "#ff0000",
            tension: 0.3,
            pointRadius: 0,
        },
    ],
};
</script>

<template>
    <Line :data="quantityData" :options="quantityGraphOptions" />
    <Line class="mt-10" :data="priceData" :options="priceGraphOptions" />
</template>

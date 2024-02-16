<script setup lang="ts">
import { Line } from 'vue-chartjs'
// import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
// ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
} from 'chart.js'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
)

const props = defineProps<{
    history: Array<{}>
}>();

const options = {
    responsive: true,
    interaction: {
        mode: 'index',
        intersect: false,
    },
    scales: {
        y: {
            min: 0,
        },
    },
}

const data = {
    labels: props.history.map((item: any) => item.date),
    datasets: [
        {
            label: '# Sold',
            data: props.history.map((item: any) => item.quantity),
            fill: false,
            borderColor: '#c319ee',
            backgroundColor: "#c319ee",
            tension: 0.3,
            pointRadius: 0,
        }
    ]
}

</script>


<template>
    <Line :data="data" :options="options" />
</template>
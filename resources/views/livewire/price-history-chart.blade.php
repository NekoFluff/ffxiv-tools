<div>
    <canvas id="price-history-chart" x-refs="price-history-chart-ref"></canvas>

    <div x-data="{
        init() {
            const ctx = document.getElementById('price-history-chart');
    
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Object.keys($wire.averagePrice),
                    datasets: [{
                        label: 'Average Price',
                        data: Object.values($wire.averagePrice),
                        borderWidth: 3,
                        borderColor: '#6b46c1',
                        backgroundColor: '#6b46c1',
                        fill: false,
                        tension: 0.3,
                        pointRadius: 0,
                        showLine: true,
                    }, {
                        label: 'Min Price',
                        data: Object.values($wire.minPrice),
                        borderWidth: 3,
                        borderColor: '#f56565',
                        backgroundColor: '#f56565',
                        showLine: true,
                    }, {
                        label: 'Max Price',
                        data: Object.values($wire.maxPrice),
                        borderWidth: 3,
                        borderColor: '#48bb78',
                        backgroundColor: '#48bb78',
                        showLine: true,
                    }, {
                        label: 'Median Price',
                        data: Object.values($wire.medianPrice),
                        borderWidth: 3,
                        borderColor: '#63b3ed',
                        backgroundColor: '#63b3ed',
                        showLine: true,
                    }],
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            type: 'linear',
                            ticks: {
                                precision: 0,
                            }
                        }
                    },
                    tension: 0.3,
                    pointRadius: 0,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Price History',
                            font: {
                                size: 16
                            }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                }
            });
        }
    }"></div>
</div>

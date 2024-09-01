<div>
    <canvas id="quantity-sold-chart" x-refs="canvas"></canvas>
    <div x-data="{
        init() {
            const ctx = document.getElementById('quantity-sold-chart');
    
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Object.keys($wire.quantitySold),
                    datasets: [{
                        label: 'Quantity Sold',
                        data: Object.values($wire.quantitySold),
                        borderWidth: 3,
                        borderColor: '#4c51bf',
                        backgroundColor: '#4c51bf',
                        fill: false,
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
                            text: 'Quantity Sold',
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

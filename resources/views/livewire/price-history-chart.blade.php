<div class="min-h-60">
    <canvas id="price-history-chart" x-refs="price-history-chart-ref"></canvas>

    <div x-on:dark-mode-toggled.window="refresh()" x-on:refresh-price-history-chart.window="refresh()"
        x-data="{
            chartColors: {
                dark: {
                    averagePrice: '#f5bc42',
                    minPrice: '#2abf3b',
                    maxPrice: '#bf2a2a',
                    gridColor: '#4a5568',
                    fontColor: '#e2e8f0'
                },
                light: {
                    averagePrice: '#f5bc42',
                    minPrice: '#2abf3b',
                    maxPrice: '#bf2a2a',
                    gridColor: '#e2e8f0',
                    fontColor: '#2d3748'
                }
            },
            refresh: function() {
                const ctx = document.getElementById('price-history-chart');
                const chart = Chart.getChart(ctx);
        
                {{-- Update data --}}
                chart.data.labels = Object.keys($wire.averagePrice);
                chart.data.datasets[0].data = Object.values($wire.averagePrice);
                chart.data.datasets[1].data = Object.values($wire.minPrice);
                chart.data.datasets[2].data = Object.values($wire.maxPrice);
        
                {{-- Recalcualte colors --}}
                const isDarkMode = localStorage.getItem('darkMode') === 'true';
                const chartColors = this.chartColors[isDarkMode ? 'dark' : 'light'];
                chart.data.datasets[0].borderColor = chartColors.averagePrice;
                chart.data.datasets[0].backgroundColor = chartColors.averagePrice;
                chart.data.datasets[1].borderColor = chartColors.minPrice;
                chart.data.datasets[1].backgroundColor = chartColors.minPrice;
                chart.data.datasets[2].borderColor = chartColors.maxPrice;
                chart.data.datasets[2].backgroundColor = chartColors.maxPrice;
                chart.options.scales.x.grid.color = chartColors.gridColor;
                chart.options.scales.x.ticks.color = chartColors.fontColor;
                chart.options.scales.y.grid.color = chartColors.gridColor;
                chart.options.scales.y.ticks.color = chartColors.fontColor;
                chart.options.plugins.title.color = chartColors.fontColor;
        
                chart.update();
                chart.resize();
            },
            init: function() {
                const ctx = document.getElementById('price-history-chart');
                const isDarkMode = localStorage.getItem('darkMode') === 'true';
        
                const chartColors = this.chartColors[isDarkMode ? 'dark' : 'light'];
        
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: Object.keys($wire.averagePrice),
                        datasets: [{
                            label: 'Average Price',
                            data: Object.values($wire.averagePrice),
                            borderWidth: 3,
                            borderColor: chartColors.averagePrice,
                            backgroundColor: chartColors.averagePrice,
                            fill: false,
                            tension: 0.3,
                            pointRadius: 0,
                            showLine: true,
                        }, {
                            label: 'Min Price',
                            data: Object.values($wire.minPrice),
                            borderWidth: 3,
                            borderColor: chartColors.minPrice,
                            backgroundColor: chartColors.minPrice,
                            showLine: true,
                        }, {
                            label: 'Max Price',
                            data: Object.values($wire.maxPrice),
                            borderWidth: 3,
                            borderColor: chartColors.maxPrice,
                            backgroundColor: chartColors.maxPrice,
                            showLine: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                grid: {
                                    color: chartColors.gridColor
                                },
                                ticks: {
                                    color: chartColors.fontColor
                                }
                            },
                            y: {
                                grid: {
                                    color: chartColors.gridColor
                                },
                                ticks: {
                                    color: chartColors.fontColor
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
                                },
                                color: chartColors.fontColor
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        }
                    }
                });
            }
        }">
    </div>
</div>

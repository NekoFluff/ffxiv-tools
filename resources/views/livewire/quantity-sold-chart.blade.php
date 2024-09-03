<div class="min-h-60">
    <canvas id="quantity-sold-chart" x-refs="canvas"></canvas>
    <div x-on:dark-mode-toggled.window="refresh()" x-on:refresh-quantity-sold-chart.window="refresh()"
        x-data="{
            chartColors: {
                dark: {
                    quantitySold: '#4c51bf',
                    gridColor: '#4a5568',
                    fontColor: '#e2e8f0'
                },
                light: {
                    quantitySold: '#6b46c1',
                    gridColor: '#e2e8f0',
                    fontColor: '#2d3748'
                }
            },
            refresh: function() {
                const ctx = document.getElementById('quantity-sold-chart');
                const chart = Chart.getChart(ctx);
        
                {{-- Update data --}}
                chart.data.labels = Object.keys($wire.quantitySold);
                chart.data.datasets[0].data = Object.values($wire.quantitySold);
        
                {{-- Recalcualte colors --}}
                const isDarkMode = localStorage.getItem('darkMode') === 'true';
                const chartColors = this.chartColors[isDarkMode ? 'dark' : 'light'];
                chart.data.datasets[0].borderColor = chartColors.quantitySold;
                chart.data.datasets[0].backgroundColor = chartColors.quantitySold;
                chart.options.scales.x.grid.color = chartColors.gridColor;
                chart.options.scales.x.ticks.color = chartColors.fontColor;
                chart.options.scales.y.grid.color = chartColors.gridColor;
                chart.options.scales.y.ticks.color = chartColors.fontColor;
                chart.options.plugins.title.color = chartColors.fontColor;
        
                chart.update();
                chart.resize();
            },
            init: function() {
                const ctx = document.getElementById('quantity-sold-chart');
                const isDarkMode = localStorage.getItem('darkMode') === 'true';
        
                const chartColors = this.chartColors[isDarkMode ? 'dark' : 'light'];
        
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: Object.keys($wire.quantitySold),
                        datasets: [{
                            label: 'Quantity Sold',
                            data: Object.values($wire.quantitySold),
                            borderWidth: 3,
                            borderColor: chartColors.quantitySold,
                            backgroundColor: chartColors.quantitySold,
                            fill: false,
                            showLine: true,
                        }],
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
                                beginAtZero: true,
                                type: 'linear',
                                ticks: {
                                    precision: 0,
                                    color: chartColors.fontColor
                                },
                                grid: {
                                    color: chartColors.gridColor
                                }
                            }
                        },
                        tension: 0.3,
                        pointRadius: 0,
                        plugins: {
                            legend: {
                                display: false,
                                color: chartColors.fontColor
                            },
                            title: {
                                display: true,
                                text: 'Quantity Sold',
                                font: {
                                    size: 16
                                },
                                color: chartColors.fontColor
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

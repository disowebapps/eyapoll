<div>
    <div class="flex justify-end mb-4">
        <select wire:model="period" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <option value="24h">Last 24 Hours</option>
            <option value="7d">Last 7 Days</option>
            <option value="30d">Last 30 Days</option>
        </select>
    </div>

    <div wire:ignore>
        <canvas id="securityMetricsChart" style="width: 100%; height: 100%;"></canvas>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let securityChart;

        document.addEventListener('livewire:load', function () {
            const ctx = document.getElementById('securityMetricsChart').getContext('2d');
            const chartData = @json($chartData);
            
            securityChart = new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            stacked: true
                        },
                        x: {
                            stacked: true
                        }
                    }
                }
            });

            Livewire.on('refreshChart', data => {
                securityChart.data = data;
                securityChart.update();
            });
        });
    </script>
    @endpush
</div>
@extends('layouts.dashboard')

@section('title', 'Dashboard - Sistema de Gestión Ganadera')

@section('content')
<div class="p-6 space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600">Resumen de tu operación ganadera</p>
    </div>

    <!-- Loading state -->
    <div id="loading" class="animate-pulse space-y-6">
        <div class="h-8 bg-gray-200 rounded w-1/4"></div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="h-32 bg-gray-200 rounded"></div>
            <div class="h-32 bg-gray-200 rounded"></div>
            <div class="h-32 bg-gray-200 rounded"></div>
            <div class="h-32 bg-gray-200 rounded"></div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div id="dashboard-content" class="hidden space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Lotes -->
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500">Total Lotes</h3>
                    <i data-lucide="package" class="h-4 w-4 text-gray-400"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900" id="totalBatches">0</div>
                <p class="text-xs text-gray-500" id="totalAnimals">0 animales en total</p>
            </div>

            <!-- Precio Promedio ARS -->
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500">Precio Promedio ARS</h3>
                    <i data-lucide="dollar-sign" class="h-4 w-4 text-gray-400"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900" id="averagePriceARS">$0</div>
                <div class="flex items-center text-xs text-green-600">
                    <i data-lucide="trending-up" class="h-3 w-3 mr-1"></i>
                    <span id="monthlyVariationARS">+0%</span> este mes
                </div>
            </div>

            <!-- Precio Promedio USD -->
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500">Precio Promedio USD</h3>
                    <i data-lucide="dollar-sign" class="h-4 w-4 text-gray-400"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900" id="averagePriceUSD">$0</div>
                <div class="flex items-center text-xs text-green-600">
                    <i data-lucide="trending-up" class="h-3 w-3 mr-1"></i>
                    <span id="monthlyVariationUSD">+0%</span> este mes
                </div>
            </div>

            <!-- Variación Mensual -->
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500">Variación Mensual</h3>
                    <i data-lucide="bar-chart-3" class="h-4 w-4 text-gray-400"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900" id="monthlyVariation">+0%</div>
                <span class="inline-block px-2 py-1 text-xs bg-green-100 text-green-600 rounded-full">
                    Tendencia positiva
                </span>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Stock por Tipo de Animal -->
            <div class="bg-white rounded-lg shadow border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Stock por Tipo de Animal</h3>
                    <p class="text-gray-600">Distribución actual de animales</p>
                </div>
                <div class="p-6">
                    <canvas id="batchesChart" width="400" height="300"></canvas>
                </div>
            </div>

            <!-- Evolución de Precios -->
            <div class="bg-white rounded-lg shadow border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Evolución de Precios</h3>
                    <p class="text-gray-600">Tendencia de precios últimos 6 meses</p>
                </div>
                <div class="p-6">
                    <canvas id="pricesChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- AI Suggestions -->
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center">
                    <i data-lucide="beef" class="mr-2 h-5 w-5 text-gray-500"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Sugerencias de IA</h3>
                </div>
                <p class="text-gray-600">Análisis inteligente de precios basado en datos históricos</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                        <h4 class="font-medium text-green-800">Oportunidad de Venta</h4>
                        <p class="text-sm text-green-700 mt-1">
                            Los precios del ganado bovino están 8% por encima del promedio histórico. Es un buen momento para vender
                            lotes de más de 24 meses.
                        </p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h4 class="font-medium text-blue-800">Tendencia del Mercado</h4>
                        <p class="text-sm text-blue-700 mt-1">
                            Se proyecta un aumento del 3-5% en los precios para el próximo trimestre debido a la demanda estacional.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let batchesChart, pricesChart;

async function fetchDashboardData() {
    try {
        // Fetch batches data
        const batchesResponse = await apiClient.get('/test/batches');
        const batches = batchesResponse.data || [];
        
        // Fetch animal types
        const typesResponse = await apiClient.get('/animal-types');
        const animalTypes = typesResponse.data || [];
        
        // Process data
        const totalBatches = batches.length;
        const totalAnimals = batches.reduce((sum, batch) => sum + (batch.quantity || 0), 0);
        const averagePriceARS = batches.length > 0 ? 
            batches.reduce((sum, batch) => sum + (parseFloat(batch.suggested_price_ars) || 0), 0) / batches.length : 0;
        const averagePriceUSD = batches.length > 0 ? 
            batches.reduce((sum, batch) => sum + (parseFloat(batch.suggested_price_usd) || 0), 0) / batches.length : 0;
        
        // Group batches by animal type
        const batchesByType = animalTypes.map(type => ({
            name: type.name,
            quantity: batches
                .filter(batch => batch.animal_type_id === type.id)
                .reduce((sum, batch) => sum + (batch.quantity || 0), 0)
        })).filter(type => type.quantity > 0);
        
        // Mock price history
        const priceHistory = [
            { month: 'Ene', ars: 45000, usd: 180 },
            { month: 'Feb', ars: 47000, usd: 185 },
            { month: 'Mar', ars: 46500, usd: 182 },
            { month: 'Abr', ars: 48000, usd: 190 },
            { month: 'May', ars: 49500, usd: 195 },
            { month: 'Jun', ars: 51000, usd: 200 }
        ];
        
        // Update UI
        updateStats({
            totalBatches,
            totalAnimals,
            averagePriceARS,
            averagePriceUSD,
            monthlyVariation: 5.2,
            batchesByType,
            priceHistory
        });
        
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
        showToast('Error al cargar datos del dashboard', 'error');
    } finally {
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('dashboard-content').classList.remove('hidden');
    }
}

function updateStats(stats) {
    document.getElementById('totalBatches').textContent = stats.totalBatches;
    document.getElementById('totalAnimals').textContent = `${stats.totalAnimals} animales en total`;
    document.getElementById('averagePriceARS').textContent = `$${Math.round(stats.averagePriceARS).toLocaleString()}`;
    document.getElementById('averagePriceUSD').textContent = `$${Math.round(stats.averagePriceUSD).toLocaleString()}`;
    document.getElementById('monthlyVariation').textContent = `+${stats.monthlyVariation}%`;
    document.getElementById('monthlyVariationARS').textContent = `+${stats.monthlyVariation}%`;
    document.getElementById('monthlyVariationUSD').textContent = `+${stats.monthlyVariation}%`;
    
    // Create charts
    createBatchesChart(stats.batchesByType);
    createPricesChart(stats.priceHistory);
}

function createBatchesChart(data) {
    const ctx = document.getElementById('batchesChart').getContext('2d');
    
    if (batchesChart) {
        batchesChart.destroy();
    }
    
    batchesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(item => item.name),
            datasets: [{
                label: 'Cantidad',
                data: data.map(item => item.quantity),
                backgroundColor: '#16a34a',
                borderColor: '#15803d',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createPricesChart(data) {
    const ctx = document.getElementById('pricesChart').getContext('2d');
    
    if (pricesChart) {
        pricesChart.destroy();
    }
    
    pricesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.month),
            datasets: [
                {
                    label: 'ARS',
                    data: data.map(item => item.ars),
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'USD',
                    data: data.map(item => item.usd),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    fetchDashboardData();
});
</script>
@endpush
@endsection
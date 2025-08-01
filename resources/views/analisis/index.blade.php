@extends('layouts.dashboard')

@section('title', 'Análisis con IA - Sistema de Gestión Ganadera')

@section('content')
<div class="p-6 space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Análisis con IA</h1>
        <p class="text-gray-600">Insights inteligentes para optimizar tu operación ganadera</p>
    </div>

    <!-- AI Insights Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Price Analysis -->
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center">
                    <i data-lucide="trending-up" class="mr-2 h-5 w-5 text-blue-600"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Análisis de Precios</h3>
                </div>
                <p class="text-gray-600">Predicciones basadas en datos históricos</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-start">
                        <i data-lucide="arrow-up" class="mr-2 h-5 w-5 text-green-600 mt-0.5"></i>
                        <div>
                            <h4 class="font-medium text-green-800">Tendencia Alcista</h4>
                            <p class="text-sm text-green-700 mt-1">
                                Los precios del ganado bovino muestran una tendencia alcista del 8.5% comparado con el mismo período del año anterior.
                            </p>
                            <p class="text-xs text-green-600 mt-2">Confianza: 87%</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-start">
                        <i data-lucide="calendar" class="mr-2 h-5 w-5 text-blue-600 mt-0.5"></i>
                        <div>
                            <h4 class="font-medium text-blue-800">Proyección Trimestral</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                Se proyecta un aumento del 3-5% en los próximos 3 meses debido a la demanda estacional de fin de año.
                            </p>
                            <p class="text-xs text-blue-600 mt-2">Confianza: 72%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Market Recommendations -->
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center">
                    <i data-lucide="lightbulb" class="mr-2 h-5 w-5 text-yellow-600"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Recomendaciones de Mercado</h3>
                </div>
                <p class="text-gray-600">Sugerencias para optimizar ventas</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="p-4 bg-orange-50 rounded-lg border border-orange-200">
                    <div class="flex items-start">
                        <i data-lucide="clock" class="mr-2 h-5 w-5 text-orange-600 mt-0.5"></i>
                        <div>
                            <h4 class="font-medium text-orange-800">Momento Óptimo de Venta</h4>
                            <p class="text-sm text-orange-700 mt-1">
                                Los lotes de 18-24 meses están en el momento óptimo para la venta. Precio actual 12% superior al histórico.
                            </p>
                            <p class="text-xs text-orange-600 mt-2">Acción recomendada: Vender en 2-3 semanas</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="flex items-start">
                        <i data-lucide="target" class="mr-2 h-5 w-5 text-purple-600 mt-0.5"></i>
                        <div>
                            <h4 class="font-medium text-purple-800">Segmentación de Mercado</h4>
                            <p class="text-sm text-purple-700 mt-1">
                                El ganado ovino tiene menor competencia en tu región. Considera aumentar la proporción en futuros lotes.
                            </p>
                            <p class="text-xs text-purple-600 mt-2">Oportunidad de nicho identificada</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Price Prediction Chart -->
    <div class="bg-white rounded-lg shadow border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Predicción de Precios</h3>
            <p class="text-gray-600">Proyección basada en algoritmos de machine learning</p>
        </div>
        <div class="p-6">
            <canvas id="predictionChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Market Factors -->
    <div class="bg-white rounded-lg shadow border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Factores de Mercado</h3>
            <p class="text-gray-600">Variables que influyen en los precios actuales</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <i data-lucide="thermometer" class="mx-auto h-8 w-8 text-red-500 mb-2"></i>
                    <h4 class="font-medium text-gray-900">Clima</h4>
                    <p class="text-sm text-gray-600">Condiciones favorables</p>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 78%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">78% positivo</p>
                    </div>
                </div>
                
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <i data-lucide="truck" class="mx-auto h-8 w-8 text-blue-500 mb-2"></i>
                    <h4 class="font-medium text-gray-900">Demanda</h4>
                    <p class="text-sm text-gray-600">Alta demanda exportadora</p>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 85%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">85% alto</p>
                    </div>
                </div>
                
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <i data-lucide="dollar-sign" class="mx-auto h-8 w-8 text-green-500 mb-2"></i>
                    <h4 class="font-medium text-gray-900">Tipo de Cambio</h4>
                    <p class="text-sm text-gray-600">Favorable para exportación</p>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 73%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">73% favorable</p>
                    </div>
                </div>
                
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <i data-lucide="wheat" class="mx-auto h-8 w-8 text-yellow-500 mb-2"></i>
                    <h4 class="font-medium text-gray-900">Costo de Alimentación</h4>
                    <p class="text-sm text-gray-600">Costos estables</p>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 45%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">45% neutro</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Learning Status -->
    <div class="bg-white rounded-lg shadow border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Estado del Modelo IA</h3>
                    <p class="text-gray-600">Información sobre el aprendizaje automático</p>
                </div>
                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    Activo
                </span>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h4 class="font-medium text-gray-900">Datos Procesados</h4>
                    <p class="text-2xl font-bold text-green-600">2,847</p>
                    <p class="text-sm text-gray-500">Registros de precios históricos</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Precisión del Modelo</h4>
                    <p class="text-2xl font-bold text-blue-600">87.3%</p>
                    <p class="text-sm text-gray-500">En predicciones a 30 días</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Última Actualización</h4>
                    <p class="text-2xl font-bold text-gray-600">2h</p>
                    <p class="text-sm text-gray-500">Aprendizaje continuo</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let predictionChart;

function createPredictionChart() {
    const ctx = document.getElementById('predictionChart').getContext('2d');
    
    // Mock prediction data
    const historicalData = [45000, 47000, 46500, 48000, 49500, 51000];
    const predictionData = [52000, 53500, 54200, 55800, 57100, 58500];
    const labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    
    predictionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Precios Históricos',
                    data: [...historicalData, ...Array(6).fill(null)],
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    tension: 0.4,
                    pointBackgroundColor: '#16a34a'
                },
                {
                    label: 'Predicción IA',
                    data: [...Array(6).fill(null), ...predictionData],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderDash: [5, 5],
                    tension: 0.4,
                    pointBackgroundColor: '#2563eb'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Precio (ARS)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Mes'
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    createPredictionChart();
});
</script>
@endpush
@endsection
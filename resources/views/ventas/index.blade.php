@extends('layouts.dashboard')

@section('title', 'Historial de Ventas - Sistema de Gestión Ganadera')

@section('content')
<div class="p-6 space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Historial de Ventas</h1>
        <p class="text-gray-600">Visualiza el historial de ventas y estadísticas</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">Total Vendido ARS</h3>
                <i data-lucide="dollar-sign" class="h-4 w-4 text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold text-gray-900" id="totalSalesARS">$0</div>
            <p class="text-xs text-gray-500">Este mes</p>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">Total Vendido USD</h3>
                <i data-lucide="dollar-sign" class="h-4 w-4 text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold text-gray-900" id="totalSalesUSD">$0</div>
            <p class="text-xs text-gray-500">Este mes</p>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">Animales Vendidos</h3>
                <i data-lucide="package" class="h-4 w-4 text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold text-gray-900" id="totalAnimalsSold">0</div>
            <p class="text-xs text-gray-500">Este mes</p>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white rounded-lg shadow border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Ventas por Mes</h3>
            <p class="text-gray-600">Evolución de ventas en los últimos 12 meses</p>
        </div>
        <div class="p-6">
            <canvas id="salesChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-lg shadow border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Ventas Recientes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lote</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comprador</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total ARS</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total USD</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Sales will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty state -->
    <div id="emptyState" class="hidden bg-white rounded-lg shadow border border-gray-200 p-12 text-center">
        <i data-lucide="dollar-sign" class="mx-auto h-12 w-12 text-gray-400"></i>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay ventas registradas</h3>
        <p class="mt-1 text-sm text-gray-500">Las ventas aparecerán aquí cuando registres transacciones.</p>
    </div>
</div>

@push('scripts')
<script>
let salesChart;

async function loadSalesData() {
    try {
        // Mock data for now since sales endpoint may not be fully implemented
        const mockSales = [];
        const mockStats = {
            totalSalesARS: 0,
            totalSalesUSD: 0,
            totalAnimalsSold: 0
        };
        
        // Mock monthly data
        const mockMonthlyData = [
            { month: 'Ene', ars: 120000, usd: 1200 },
            { month: 'Feb', ars: 150000, usd: 1500 },
            { month: 'Mar', ars: 135000, usd: 1350 },
            { month: 'Abr', ars: 180000, usd: 1800 },
            { month: 'May', ars: 165000, usd: 1650 },
            { month: 'Jun', ars: 200000, usd: 2000 }
        ];
        
        // Update stats
        document.getElementById('totalSalesARS').textContent = `$${mockStats.totalSalesARS.toLocaleString()}`;
        document.getElementById('totalSalesUSD').textContent = `$${mockStats.totalSalesUSD.toLocaleString()}`;
        document.getElementById('totalAnimalsSold').textContent = mockStats.totalAnimalsSold;
        
        // Render chart
        createSalesChart(mockMonthlyData);
        
        // Render table
        if (mockSales.length === 0) {
            document.getElementById('emptyState').classList.remove('hidden');
        } else {
            renderSalesTable(mockSales);
        }
        
    } catch (error) {
        console.error('Error loading sales data:', error);
        showToast('Error al cargar datos de ventas', 'error');
        document.getElementById('emptyState').classList.remove('hidden');
    }
}

function createSalesChart(data) {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    if (salesChart) {
        salesChart.destroy();
    }
    
    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.month),
            datasets: [
                {
                    label: 'Ventas ARS',
                    data: data.map(item => item.ars),
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Ventas USD',
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
                    beginAtZero: true
                }
            }
        }
    });
}

function renderSalesTable(sales) {
    const tbody = document.getElementById('salesTableBody');
    tbody.innerHTML = '';
    
    sales.forEach(sale => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${new Date(sale.sale_date).toLocaleDateString()}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">Lote #${sale.batch_id}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${sale.buyer_name || 'N/A'}</div>
                <div class="text-sm text-gray-500">${sale.buyer_contact || ''}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${sale.quantity_sold || 0} animales</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">$${parseFloat(sale.total_amount_ars || 0).toLocaleString()}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">$${parseFloat(sale.total_amount_usd || 0).toLocaleString()}</div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadSalesData();
});
</script>
@endpush
@endsection
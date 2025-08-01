@extends('layouts.dashboard')

@section('title', 'Gestión de Lotes - Sistema de Gestión Ganadera')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Lotes</h1>
            <p class="text-gray-600">Administra todos tus lotes de ganado</p>
        </div>
        <a href="{{ route('lotes.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center">
            <i data-lucide="plus" class="mr-2 h-4 w-4"></i>
            Nuevo Lote
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="animalTypeFilter" class="block text-sm font-medium text-gray-700">Tipo de Animal</label>
                <select id="animalTypeFilter" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                    <option value="">Todos</option>
                </select>
            </div>
            <div>
                <label for="statusFilter" class="block text-sm font-medium text-gray-700">Estado</label>
                <select id="statusFilter" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                    <option value="">Todos</option>
                    <option value="available">Disponible</option>
                    <option value="sold">Vendido</option>
                    <option value="reserved">Reservado</option>
                </select>
            </div>
            <div>
                <label for="searchFilter" class="block text-sm font-medium text-gray-700">Buscar</label>
                <input type="text" id="searchFilter" placeholder="Buscar en notas..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="flex items-end">
                <button onclick="loadBatches()" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 w-full">
                    <i data-lucide="search" class="mr-2 h-4 w-4"></i>
                    Buscar
                </button>
            </div>
        </div>
    </div>

    <!-- Loading state -->
    <div id="loading" class="bg-white rounded-lg shadow border border-gray-200 p-6">
        <div class="animate-pulse space-y-4">
            <div class="h-4 bg-gray-200 rounded w-1/4"></div>
            <div class="space-y-2">
                <div class="h-4 bg-gray-200 rounded"></div>
                <div class="h-4 bg-gray-200 rounded"></div>
                <div class="h-4 bg-gray-200 rounded"></div>
            </div>
        </div>
    </div>

    <!-- Batches Table -->
    <div id="batchesContainer" class="hidden bg-white rounded-lg shadow border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Lista de Lotes</h3>
            <p class="text-gray-600">Total: <span id="totalBatches">0</span> lotes</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lote</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Edad/Peso</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio ARS</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio USD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody id="batchesTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Batches will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty state -->
    <div id="emptyState" class="hidden bg-white rounded-lg shadow border border-gray-200 p-12 text-center">
        <i data-lucide="package" class="mx-auto h-12 w-12 text-gray-400"></i>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay lotes</h3>
        <p class="mt-1 text-sm text-gray-500">Comienza creando tu primer lote de ganado.</p>
        <div class="mt-6">
            <a href="{{ route('lotes.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 inline-flex items-center">
                <i data-lucide="plus" class="mr-2 h-4 w-4"></i>
                Nuevo Lote
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
let allBatches = [];
let animalTypes = [];

async function loadBatches() {
    try {
        document.getElementById('loading').classList.remove('hidden');
        document.getElementById('batchesContainer').classList.add('hidden');
        document.getElementById('emptyState').classList.add('hidden');
        
        // Build query parameters
        const params = new URLSearchParams();
        const animalTypeFilter = document.getElementById('animalTypeFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        const searchFilter = document.getElementById('searchFilter').value;
        
        if (animalTypeFilter) params.append('animal_type_id', animalTypeFilter);
        if (statusFilter) params.append('status', statusFilter);
        if (searchFilter) params.append('search', searchFilter);
        
        // Fetch batches
        const response = await apiClient.get(`/test/batches?${params.toString()}`);
        allBatches = response.data || [];
        
        document.getElementById('totalBatches').textContent = allBatches.length;
        
        if (allBatches.length === 0) {
            document.getElementById('emptyState').classList.remove('hidden');
        } else {
            renderBatchesTable();
            document.getElementById('batchesContainer').classList.remove('hidden');
        }
        
    } catch (error) {
        console.error('Error loading batches:', error);
        showToast('Error al cargar los lotes', 'error');
    } finally {
        document.getElementById('loading').classList.add('hidden');
    }
}

function renderBatchesTable() {
    const tbody = document.getElementById('batchesTableBody');
    tbody.innerHTML = '';
    
    allBatches.forEach(batch => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">Lote #${batch.id}</div>
                <div class="text-sm text-gray-500">Creado: ${new Date(batch.created_at).toLocaleDateString()}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${batch.animal_type?.name || 'N/A'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${batch.quantity || 0} animales</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${batch.age_months || 0} meses</div>
                <div class="text-sm text-gray-500">${batch.average_weight_kg || 0} kg</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">$${parseFloat(batch.suggested_price_ars || 0).toLocaleString()}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">$${parseFloat(batch.suggested_price_usd || 0).toLocaleString()}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusClasses(batch.status)}">
                    ${getStatusLabel(batch.status)}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button onclick="viewBatch(${batch.id})" class="text-blue-600 hover:text-blue-900">Ver</button>
                ${batch.status !== 'sold' ? `<button onclick="markAsSold(${batch.id})" class="text-green-600 hover:text-green-900">Marcar como vendido</button>` : ''}
                <button onclick="deleteBatch(${batch.id})" class="text-red-600 hover:text-red-900">Eliminar</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function getStatusClasses(status) {
    switch (status) {
        case 'available':
            return 'bg-green-100 text-green-800';
        case 'sold':
            return 'bg-gray-100 text-gray-800';
        case 'reserved':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getStatusLabel(status) {
    switch (status) {
        case 'available':
            return 'Disponible';
        case 'sold':
            return 'Vendido';
        case 'reserved':
            return 'Reservado';
        default:
            return 'Desconocido';
    }
}

async function loadAnimalTypes() {
    try {
        const response = await apiClient.get('/animal-types');
        animalTypes = response.data || [];
        
        const select = document.getElementById('animalTypeFilter');
        animalTypes.forEach(type => {
            const option = document.createElement('option');
            option.value = type.id;
            option.textContent = type.name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading animal types:', error);
    }
}

function viewBatch(id) {
    // Implement view functionality
    showToast(`Ver lote #${id} (funcionalidad pendiente)`);
}

async function markAsSold(id) {
    if (!confirm('¿Estás seguro de que quieres marcar este lote como vendido?')) {
        return;
    }
    
    try {
        const response = await fetch(`${apiClient.baseURL}/test/batches/${id}/mark-as-sold`, {
            method: 'PATCH',
            headers: apiClient.getAuthHeaders(),
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Lote marcado como vendido exitosamente');
            loadBatches(); // Reload the table
        } else {
            showToast(data.message || 'Error al marcar el lote como vendido', 'error');
        }
    } catch (error) {
        console.error('Error marking batch as sold:', error);
        showToast('Error al marcar el lote como vendido', 'error');
    }
}

async function deleteBatch(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar este lote?')) {
        return;
    }
    
    try {
        const response = await apiClient.delete(`/test/batches/${id}`);
        
        if (response.success) {
            showToast('Lote eliminado exitosamente');
            loadBatches(); // Reload the table
        } else {
            showToast(response.message || 'Error al eliminar el lote', 'error');
        }
    } catch (error) {
        console.error('Error deleting batch:', error);
        showToast('Error al eliminar el lote', 'error');
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadAnimalTypes();
    loadBatches();
});
</script>
@endpush
@endsection
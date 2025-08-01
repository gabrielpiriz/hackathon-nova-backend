@extends('layouts.dashboard')

@section('title', 'Nuevo Lote - Sistema de Gestión Ganadera')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center">
        <a href="{{ route('lotes.index') }}" class="mr-4 text-gray-500 hover:text-gray-700">
            <i data-lucide="arrow-left" class="h-5 w-5"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Nuevo Lote</h1>
            <p class="text-gray-600">Crear un nuevo lote de ganado</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Información del Lote</h3>
            <p class="text-gray-600">Completa los datos del nuevo lote</p>
        </div>
        
        <form id="createBatchForm" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tipo de Animal -->
                <div>
                    <label for="animal_type_id" class="block text-sm font-medium text-gray-700">Tipo de Animal *</label>
                    <select id="animal_type_id" name="animal_type_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        <option value="">Selecciona un tipo</option>
                    </select>
                </div>

                <!-- Cantidad -->
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Cantidad de Animales *</label>
                    <input type="number" id="quantity" name="quantity" min="1" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>

                <!-- Edad -->
                <div>
                    <label for="age_months" class="block text-sm font-medium text-gray-700">Edad (meses) *</label>
                    <input type="number" id="age_months" name="age_months" min="1" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>

                <!-- Peso Promedio -->
                <div>
                    <label for="average_weight_kg" class="block text-sm font-medium text-gray-700">Peso Promedio (kg) *</label>
                    <input type="number" id="average_weight_kg" name="average_weight_kg" step="0.01" min="0" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>

                <!-- Precio Sugerido ARS -->
                <div>
                    <label for="suggested_price_ars" class="block text-sm font-medium text-gray-700">Precio Sugerido ARS *</label>
                    <input type="number" id="suggested_price_ars" name="suggested_price_ars" step="0.01" min="0" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>

                <!-- Precio Sugerido USD -->
                <div>
                    <label for="suggested_price_usd" class="block text-sm font-medium text-gray-700">Precio Sugerido USD *</label>
                    <input type="number" id="suggested_price_usd" name="suggested_price_usd" step="0.01" min="0" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Notas -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notas</label>
                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" placeholder="Información adicional sobre el lote..."></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('lotes.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Cancelar
                </a>
                <button type="submit" id="submitBtn" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                    <i data-lucide="loader-2" class="mr-2 h-4 w-4 animate-spin hidden" id="submitLoader"></i>
                    <span id="submitText">Crear Lote</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
async function loadAnimalTypes() {
    try {
        const response = await apiClient.get('/animal-types');
        const animalTypes = response.data || [];
        
        const select = document.getElementById('animal_type_id');
        animalTypes.forEach(type => {
            const option = document.createElement('option');
            option.value = type.id;
            option.textContent = type.name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading animal types:', error);
        showToast('Error al cargar tipos de animales', 'error');
    }
}

document.getElementById('createBatchForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    const submitBtn = document.getElementById('submitBtn');
    const submitLoader = document.getElementById('submitLoader');
    const submitText = document.getElementById('submitText');
    
    // Show loading state
    submitBtn.disabled = true;
    submitLoader.classList.remove('hidden');
    submitText.textContent = 'Creando...';
    
    try {
        const response = await apiClient.post('/test/batches', data);
        
        if (response.success) {
            showToast('Lote creado exitosamente');
            // Redirect to lotes list
            window.location.href = '{{ route("lotes.index") }}';
        } else {
            showToast(response.message || 'Error al crear el lote', 'error');
            
            // Show validation errors if any
            if (response.errors) {
                Object.keys(response.errors).forEach(field => {
                    const fieldElement = document.getElementById(field);
                    if (fieldElement) {
                        fieldElement.classList.add('border-red-500');
                        
                        // Create error message
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'text-red-500 text-sm mt-1';
                        errorDiv.textContent = response.errors[field][0];
                        
                        // Remove existing error
                        const existingError = fieldElement.parentNode.querySelector('.text-red-500');
                        if (existingError) {
                            existingError.remove();
                        }
                        
                        fieldElement.parentNode.appendChild(errorDiv);
                    }
                });
            }
        }
    } catch (error) {
        console.error('Create batch error:', error);
        showToast('Error de conexión', 'error');
    } finally {
        // Reset loading state
        submitBtn.disabled = false;
        submitLoader.classList.add('hidden');
        submitText.textContent = 'Crear Lote';
    }
});

// Clear validation errors on input
document.querySelectorAll('input, select, textarea').forEach(element => {
    element.addEventListener('input', function() {
        this.classList.remove('border-red-500');
        const errorDiv = this.parentNode.querySelector('.text-red-500');
        if (errorDiv) {
            errorDiv.remove();
        }
    });
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadAnimalTypes();
});
</script>
@endpush
@endsection
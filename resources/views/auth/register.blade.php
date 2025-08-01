@extends('layouts.app')

@section('title', 'Crear Cuenta - Sistema de Gestión Ganadera')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-50 to-blue-50 p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg border border-gray-200">
        <!-- Header -->
        <div class="p-6 text-center border-b border-gray-200">
            <div class="flex justify-center mb-4">
                <i data-lucide="beef" class="h-12 w-12 text-green-600"></i>
            </div>
            <h1 class="text-2xl font-semibold text-gray-900">Crear Cuenta</h1>
            <p class="text-gray-600 mt-2">Regístrate como productor ganadero</p>
        </div>
        
        <!-- Content -->
        <div class="p-6">
            <form id="registerForm" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="first_name" class="text-sm font-medium text-gray-700">Nombre</label>
                        <input 
                            id="first_name" 
                            name="first_name" 
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                            required 
                        />
                    </div>
                    <div class="space-y-2">
                        <label for="last_name" class="text-sm font-medium text-gray-700">Apellido</label>
                        <input 
                            id="last_name" 
                            name="last_name" 
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                            required 
                        />
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-gray-700">Email</label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        required 
                    />
                </div>
                
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-gray-700">Contraseña</label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        required 
                    />
                    <p class="text-xs text-gray-500">
                        Mínimo 8 caracteres, mayúsculas, minúsculas, números y caracteres especiales
                    </p>
                </div>
                
                <div class="space-y-2">
                    <label for="password_confirmation" class="text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                    <input 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        required 
                    />
                </div>
                
                <button 
                    type="submit" 
                    id="registerBtn"
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                >
                    <i data-lucide="loader-2" class="mr-2 h-4 w-4 animate-spin hidden" id="registerLoader"></i>
                    <span id="registerText">Crear Cuenta</span>
                </button>
            </form>
            
            <div class="mt-4 text-center text-sm text-gray-600">
                ¿Ya tienes cuenta? 
                <a href="{{ route('login') }}" class="text-green-600 hover:underline">Inicia sesión</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function validatePassword(password) {
    const minLength = password.length >= 8;
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /\d/.test(password);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    return minLength && hasUpper && hasLower && hasNumber && hasSpecial;
}

document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    const registerBtn = document.getElementById('registerBtn');
    const registerLoader = document.getElementById('registerLoader');
    const registerText = document.getElementById('registerText');
    
    // Validate password
    if (!validatePassword(data.password)) {
        showToast('La contraseña debe tener al menos 8 caracteres, mayúsculas, minúsculas, números y caracteres especiales', 'error');
        return;
    }
    
    if (data.password !== data.password_confirmation) {
        showToast('Las contraseñas no coinciden', 'error');
        return;
    }
    
    // Show loading state
    registerBtn.disabled = true;
    registerLoader.classList.remove('hidden');
    registerText.textContent = 'Creando cuenta...';
    
    try {
        const response = await apiClient.post('/register', data);
        
        if (response.success) {
            // Store token
            localStorage.setItem('token', response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
            
            showToast('Cuenta creada exitosamente');
            
            // Redirect to dashboard
            window.location.href = '{{ route("dashboard") }}';
        } else {
            showToast(response.message || 'Error al crear la cuenta', 'error');
        }
    } catch (error) {
        console.error('Register error:', error);
        showToast('Error de conexión', 'error');
    } finally {
        // Reset loading state
        registerBtn.disabled = false;
        registerLoader.classList.add('hidden');
        registerText.textContent = 'Crear Cuenta';
    }
});
</script>
@endpush
@endsection
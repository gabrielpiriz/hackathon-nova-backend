@extends('layouts.app')

@section('title', 'Iniciar Sesión - Sistema de Gestión Ganadera')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-50 to-blue-50 p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg border border-gray-200">
        <!-- Header -->
        <div class="p-6 text-center border-b border-gray-200">
            <div class="flex justify-center mb-4">
                <i data-lucide="beef" class="h-12 w-12 text-green-600"></i>
            </div>
            <h1 class="text-2xl font-semibold text-gray-900">Iniciar Sesión</h1>
            <p class="text-gray-600 mt-2">Ingresa a tu cuenta de productor</p>
        </div>
        
        <!-- Content -->
        <div class="p-6">
            <form id="loginForm" class="space-y-4">
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-gray-700">Email</label>
                    <input 
                        id="email" 
                        type="email" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        required 
                    />
                </div>
                
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-gray-700">Contraseña</label>
                    <input 
                        id="password" 
                        type="password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        required 
                    />
                </div>
                
                <button 
                    type="submit" 
                    id="loginBtn"
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                >
                    <i data-lucide="loader-2" class="mr-2 h-4 w-4 animate-spin hidden" id="loginLoader"></i>
                    <span id="loginText">Iniciar Sesión</span>
                </button>
            </form>
            
            <div class="mt-4 text-center text-sm text-gray-600">
                ¿No tienes cuenta? 
                <a href="{{ route('register') }}" class="text-green-600 hover:underline">Regístrate</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const loginBtn = document.getElementById('loginBtn');
    const loginLoader = document.getElementById('loginLoader');
    const loginText = document.getElementById('loginText');
    
    // Show loading state
    loginBtn.disabled = true;
    loginLoader.classList.remove('hidden');
    loginText.textContent = 'Iniciando...';
    
    try {
        const response = await apiClient.post('/login', { email, password });
        
        if (response.success) {
            // Store token
            localStorage.setItem('token', response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
            
            showToast('Inicio de sesión exitoso');
            
            // Redirect to dashboard
            window.location.href = '{{ route("dashboard") }}';
        } else {
            showToast(response.message || 'Credenciales inválidas', 'error');
        }
    } catch (error) {
        console.error('Login error:', error);
        showToast('Error de conexión', 'error');
    } finally {
        // Reset loading state
        loginBtn.disabled = false;
        loginLoader.classList.add('hidden');
        loginText.textContent = 'Iniciar Sesión';
    }
});
</script>
@endpush
@endsection
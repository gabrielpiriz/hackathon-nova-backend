<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard - Sistema de Gestión Ganadera')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-md">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center">
                    <i data-lucide="beef" class="h-8 w-8 text-green-600 mr-3"></i>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">Ganadería</h1>
                        <p class="text-sm text-gray-500">Sistema de Gestión</p>
                    </div>
                </div>
            </div>
            
            <nav class="mt-6">
                <div class="px-6 space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-green-100 text-green-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i data-lucide="bar-chart-3" class="mr-3 h-5 w-5"></i>
                        Dashboard
                    </a>
                    
                    <a href="{{ route('lotes.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('lotes.*') ? 'bg-green-100 text-green-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i data-lucide="package" class="mr-3 h-5 w-5"></i>
                        Lotes
                    </a>
                    
                    <a href="{{ route('ventas.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('ventas.*') ? 'bg-green-100 text-green-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i data-lucide="dollar-sign" class="mr-3 h-5 w-5"></i>
                        Ventas
                    </a>
                    
                    <a href="{{ route('analisis.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('analisis.*') ? 'bg-green-100 text-green-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i data-lucide="trending-up" class="mr-3 h-5 w-5"></i>
                        Análisis IA
                    </a>
                </div>
            </nav>
            
            <div class="absolute bottom-0 w-64 p-6 border-t border-gray-200">
                <div class="flex items-center">
                    <div class="h-9 w-9 bg-green-500 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-white" id="userInitials">U</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-700" id="userName">Usuario</p>
                        <button onclick="logout()" class="text-xs text-gray-500 hover:text-gray-700">Cerrar sesión</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <div class="flex-1 overflow-auto">
            @yield('content')
        </div>
    </div>
    
    <!-- Toast container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50"></div>
    
    <!-- API Client -->
    <script>
        // API Client for frontend
        class ApiClient {
            constructor() {
                this.baseURL = '{{ url("/api") }}';
            }
            
            getAuthHeaders() {
                const token = localStorage.getItem('token');
                return {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...(token && { 'Authorization': `Bearer ${token}` }),
                };
            }
            
            async get(endpoint) {
                const response = await fetch(`${this.baseURL}${endpoint}`, {
                    headers: this.getAuthHeaders(),
                });
                return response.json();
            }
            
            async post(endpoint, data) {
                const response = await fetch(`${this.baseURL}${endpoint}`, {
                    method: 'POST',
                    headers: this.getAuthHeaders(),
                    body: JSON.stringify(data),
                });
                return response.json();
            }
            
            async delete(endpoint) {
                const response = await fetch(`${this.baseURL}${endpoint}`, {
                    method: 'DELETE',
                    headers: this.getAuthHeaders(),
                });
                return response.json();
            }
        }
        
        const apiClient = new ApiClient();
        
        // Toast notifications
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `p-4 mb-2 rounded-lg shadow-lg transition-all duration-300 transform ${
                type === 'error' ? 'bg-red-500 text-white' : 'bg-green-500 text-white'
            }`;
            toast.textContent = message;
            
            toastContainer.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }
        
        // Auth functions
        function checkAuth() {
            const token = localStorage.getItem('token');
            const user = localStorage.getItem('user');
            
            if (!token || !user) {
                window.location.href = '{{ route("login") }}';
                return false;
            }
            
            // Update user info in sidebar
            const userData = JSON.parse(user);
            document.getElementById('userName').textContent = userData.first_name + ' ' + userData.last_name;
            document.getElementById('userInitials').textContent = userData.first_name.charAt(0).toUpperCase();
            
            return true;
        }
        
        function logout() {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '{{ route("login") }}';
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
            checkAuth();
        });
    </script>
    
    @stack('scripts')
</body>
</html>
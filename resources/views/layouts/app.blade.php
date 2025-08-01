<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Gestión Ganadera')</title>
    
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
    
    <style>
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @yield('content')
    </div>
    
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
            const toastContainer = document.getElementById('toast-container') || createToastContainer();
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-300 transform ${
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
        
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-0 right-0 z-50';
            document.body.appendChild(container);
            return container;
        }
        
        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
    
    @stack('scripts')
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-blue-800 text-white">
            <div class="p-4">
                <h1 class="text-2xl font-bold">Admin Panel</h1>
            </div>
            <nav class="mt-4">
                <a href="{{ route('admin.dashboard') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                <a href="{{ route('admin.users') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('admin.users') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-users mr-2"></i> Users
                </a>
                <a href="{{ route('admin.products') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('admin.products') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-box mr-2"></i> Products
                </a>
                <a href="{{ route('admin.raw-materials') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('admin.raw-materials') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-boxes mr-2"></i> Raw Materials
                </a>
                <a href="{{ route('admin.transactions') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('admin.transactions') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-exchange-alt mr-2"></i> Transactions
                </a>
                <a href="{{ route('admin.reports') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('admin.reports') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-chart-bar mr-2"></i> Reports
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Top Navigation -->
            <div class="bg-white shadow">
                <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-semibold">@yield('header')</h2>
                    </div>
                    <div class="flex items-center">
                        <span class="mr-4">{{ session('user')['name'] }}</span>
                        <button onclick="logout()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="container mx-auto px-6 py-8">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="modalContainer"></div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Global AJAX Setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': `Bearer {{ session('jwt_token') }}`
            }
        });

        // Global Error Handler
        $(document).ajaxError(function(event, jqXHR, settings, error) {
            if (jqXHR.status === 401) {
                Swal.fire({
                    title: 'Session Expired',
                    text: 'Your session has expired. Please login again.',
                    icon: 'warning',
                    confirmButtonText: 'Login'
                }).then(() => {
                    window.location.href = '/login';
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: jqXHR.responseJSON?.message || 'An error occurred',
                    icon: 'error'
                });
            }
        });

        // Logout Function
        async function logout() {
            try {
                await $.post('/logout');
                window.location.href = '/login';
            } catch (error) {
                console.error('Logout error:', error);
            }
        }

        // Show Loading
        function showLoading() {
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        // Hide Loading
        function hideLoading() {
            Swal.close();
        }
    </script>
    @stack('scripts')
</body>
</html> 
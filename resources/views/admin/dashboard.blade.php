@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('header', 'Dashboard Overview')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stats Cards -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                <i class="fas fa-users text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500">Total Users</p>
                <h3 class="text-2xl font-bold" id="totalUsers">0</h3>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-500">
                <i class="fas fa-box text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500">Total Products</p>
                <h3 class="text-2xl font-bold" id="totalProducts">0</h3>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                <i class="fas fa-boxes text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500">Raw Materials</p>
                <h3 class="text-2xl font-bold" id="totalRawMaterials">0</h3>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                <i class="fas fa-exchange-alt text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500">Transactions</p>
                <h3 class="text-2xl font-bold" id="totalTransactions">0</h3>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">Recent Activities</h2>
    <div class="space-y-4" id="recentActivities">
        <!-- Activities will be loaded dynamically -->
    </div>
</div>

@include('admin.components.user-management')
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        loadDashboardStats();
        loadRecentActivities();
    });

    async function loadDashboardStats() {
        try {
            showLoading();
            
            // Load users count
            const usersResponse = await $.get('/admin/viewuser');
            $('#totalUsers').text(usersResponse.data?.length || 0);
            
            // Load products count
            const productsResponse = await $.get('/products');
            $('#totalProducts').text(productsResponse.data?.length || 0);
            
            // Load raw materials count
            const materialsResponse = await $.get('/raw-materials');
            $('#totalRawMaterials').text(materialsResponse.data?.length || 0);
            
            // Load transactions count
            const transactionsResponse = await $.get('/admin/transaction-view');
            $('#totalTransactions').text(transactionsResponse.data?.length || 0);
            
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load dashboard statistics'
            });
        } finally {
            hideLoading();
        }
    }

    async function loadRecentActivities() {
        try {
            // This is a placeholder for recent activities
            // You'll need to implement the actual endpoint
            const activities = [
                {
                    type: 'user_created',
                    message: 'New user account created',
                    timestamp: new Date().toISOString()
                },
                {
                    type: 'product_updated',
                    message: 'Product stock updated',
                    timestamp: new Date().toISOString()
                }
            ];

            const container = $('#recentActivities');
            container.empty();

            activities.forEach(activity => {
                const icon = getActivityIcon(activity.type);
                const timeAgo = new Date(activity.timestamp).toLocaleString();

                container.append(`
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-blue-100 text-blue-500">
                            <i class="fas ${icon}"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-700">${activity.message}</p>
                            <p class="text-sm text-gray-500">${timeAgo}</p>
                        </div>
                    </div>
                `);
            });

        } catch (error) {
            console.error('Error loading recent activities:', error);
        }
    }

    function getActivityIcon(type) {
        const icons = {
            user_created: 'fa-user-plus',
            product_updated: 'fa-box',
            default: 'fa-info-circle'
        };
        return icons[type] || icons.default;
    }
</script>
@endpush
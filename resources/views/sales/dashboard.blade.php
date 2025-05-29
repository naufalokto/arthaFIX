@extends('layouts.sales')

@section('title', 'Dashboard')
@section('header', 'Sales Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Quick Stats -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Total Products</h3>
        <p class="text-3xl font-bold text-blue-600" id="totalProducts">0</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Low Stock Items</h3>
        <p class="text-3xl font-bold text-yellow-600" id="lowStockItems">0</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Raw Materials</h3>
        <p class="text-3xl font-bold text-green-600" id="totalRawMaterials">0</p>
    </div>
</div>

<!-- Recent Activities -->
<div class="mt-8">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Stock Updates</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="recentStockUpdates">
                        <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load initial data
    loadDashboardData();

    function loadDashboardData() {
        // Get stock data
        $.get('{{ route("sales.products.stock") }}', function(response) {
            if (response && Array.isArray(response)) {
                updateDashboardStats(response);
                updateRecentStockUpdates(response);
            }
        });

        // Get raw materials data
        $.get('{{ route("sales.raw-materials.index") }}', function(response) {
            if (response && Array.isArray(response)) {
                $('#totalRawMaterials').text(response.length);
            }
        });
    }

    function updateDashboardStats(stockData) {
        $('#totalProducts').text(stockData.length);
        
        const lowStockItems = stockData.filter(item => item.stock < 10).length;
        $('#lowStockItems').text(lowStockItems);
    }

    function updateRecentStockUpdates(stockData) {
        const tbody = $('#recentStockUpdates');
        tbody.empty();

        stockData.slice(0, 5).forEach(item => {
            const status = item.stock < 10 ? 
                '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Low Stock</span>' :
                '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">In Stock</span>';

            tbody.append(`
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.product.product_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.stock}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp ${item.product.price.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${status}</td>
                </tr>
            `);
        });
    }
});
</script>
@endpush 
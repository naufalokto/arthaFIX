@extends('layouts.sales')

@section('title', 'View Stock')
@section('header', 'Stock Management')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-700">Product Stock List</h3>
            <button onclick="showSetProductModal()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                Add New Product
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="stockTableBody">
                    <!-- Data will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Update Stock Modal -->
<div id="updateStockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Update Stock</h3>
            <form id="updateStockForm" class="space-y-4">
                <input type="hidden" id="update_product_id" name="product_id">
                <div>
                    <label for="update_stock" class="block text-sm font-medium text-gray-700">New Stock Quantity</label>
                    <input type="number" name="stock" id="update_stock" required min="0"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideModal('updateStockModal')"
                        class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    loadStockData();

    function loadStockData() {
        $.get('{{ route("sales.products.stock") }}', function(response) {
            if (response && Array.isArray(response)) {
                updateStockTable(response);
            }
        });
    }

    function updateStockTable(stockData) {
        const tbody = $('#stockTableBody');
        tbody.empty();

        stockData.forEach(item => {
            const status = item.stock < 10 ? 
                '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Low Stock</span>' :
                '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">In Stock</span>';

            tbody.append(`
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.product.product_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp ${item.product.price.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.stock}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.product.note || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${status}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <button onclick="showUpdateStockModal(${item.product_id}, ${item.stock})"
                            class="text-blue-600 hover:text-blue-900">Update Stock</button>
                    </td>
                </tr>
            `);
        });
    }

    $('#updateStockForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serializeArray();
        const data = {};
        formData.forEach(item => {
            data[item.name] = item.value;
        });

        $.ajax({
            url: '{{ route("sales.products.stock.update") }}',
            method: 'PUT',
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(response) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Stock has been updated successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    hideModal('updateStockModal');
                    $('#updateStockForm')[0].reset();
                    loadStockData();
                });
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'An error occurred while updating the stock';
                Swal.fire({
                    title: 'Error!',
                    text: message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});

function showUpdateStockModal(productId, currentStock) {
    $('#update_product_id').val(productId);
    $('#update_stock').val(currentStock);
    $('#updateStockModal').removeClass('hidden');
}
</script>
@endpush 
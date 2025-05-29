<!-- Set Raw Material Modal -->
<div id="setRawMaterialModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Set Raw Material</h3>
            <form id="setRawMaterialForm" class="space-y-4">
                <div>
                    <label for="raw_material_name" class="block text-sm font-medium text-gray-700">Material Name</label>
                    <input type="text" name="raw_material_name" id="raw_material_name" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="material_price" class="block text-sm font-medium text-gray-700">Price</label>
                    <input type="number" name="price" id="material_price" required min="0"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
                    <input type="text" name="supplier" id="supplier" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideModal('setRawMaterialModal')"
                        class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Save Material
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#setRawMaterialForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serializeArray();
        const data = {};
        formData.forEach(item => {
            data[item.name] = item.value;
        });

        $.ajax({
            url: '{{ route("sales.raw-materials.store") }}',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(response) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Raw material has been added successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    hideModal('setRawMaterialModal');
                    $('#setRawMaterialForm')[0].reset();
                    // Refresh dashboard data if we're on dashboard
                    if (typeof loadDashboardData === 'function') {
                        loadDashboardData();
                    }
                });
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'An error occurred while saving the raw material';
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
</script>
@endpush 
@section('title', 'User Management')

<div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
        <button onclick="openCreateUserModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            <i class="fas fa-plus mr-2"></i> Add New User
        </button>
    </div>

    <!-- User Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody" class="divide-y divide-gray-200">
                <!-- Data will be loaded dynamically -->
            </tbody>
        </table>
    </div>
</div>

<!-- Create User Modal Template -->
<template id="createUserModal">
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Create New User</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="createUserForm" onsubmit="handleCreateUser(event)">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="firstname">
                        First Name
                    </label>
                    <input type="text" id="firstname" name="firstname" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="lastname">
                        Last Name
                    </label>
                    <input type="text" id="lastname" name="lastname" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input type="email" id="email" name="email" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <input type="password" id="password" name="password" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="role">
                        Role
                    </label>
                    <select id="role" name="role" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="Manager">Manager</option>
                        <option value="Sales">Sales</option>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal()"
                        class="bg-gray-500 text-white px-4 py-2 rounded mr-2 hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

@push('scripts')
<script>
    // Load users on page load
    $(document).ready(function() {
        loadUsers();
    });

    // Load users function
    async function loadUsers() {
        try {
            showLoading();
            const response = await $.get('/admin/viewuser');
            const users = response.data || [];
            
            const tbody = $('#userTableBody');
            tbody.empty();
            
            users.forEach(user => {
                tbody.append(`
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">${user.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${user.email}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                ${user.role}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        } catch (error) {
            console.error('Error loading users:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load users'
            });
        } finally {
            hideLoading();
        }
    }

    // Open create user modal
    function openCreateUserModal() {
        const template = document.querySelector('#createUserModal');
        const modal = template.content.cloneNode(true);
        document.getElementById('modalContainer').appendChild(modal);
    }

    // Close modal
    function closeModal() {
        const modal = document.querySelector('#modalContainer > div');
        if (modal) {
            modal.remove();
        }
    }

    // Handle create user
    async function handleCreateUser(event) {
        event.preventDefault();
        
        try {
            showLoading();
            
            const formData = {
                firstname: $('#firstname').val(),
                lastname: $('#lastname').val(),
                email: $('#email').val(),
                password: $('#password').val(),
                role: $('#role').val()
            };

            await $.ajax({
                url: '/admin/create-account',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData)
            });

            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'User created successfully'
            });

            closeModal();
            loadUsers();
        } catch (error) {
            console.error('Error creating user:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.responseJSON?.message || 'Failed to create user'
            });
        } finally {
            hideLoading();
        }
    }

    // Delete user
    async function deleteUser(userId) {
        try {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            });

            if (result.isConfirmed) {
                showLoading();
                
                await $.ajax({
                    url: `/admin/delete-user?id=${userId}`,
                    method: 'DELETE'
                });

                Swal.fire(
                    'Deleted!',
                    'User has been deleted.',
                    'success'
                );

                loadUsers();
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.responseJSON?.message || 'Failed to delete user'
            });
        } finally {
            hideLoading();
        }
    }
</script>
@endpush 
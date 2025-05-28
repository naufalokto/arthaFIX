<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-header {
            background: #2563eb;
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-nav {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }
        .admin-nav a {
            padding: 12px 16px;
            background: white;
            color: #2563eb;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .admin-nav a:hover {
            background: #1d4ed8;
            color: white;
        }
        .admin-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }
        .admin-card h3 {
            color: #2563eb;
            margin-bottom: 16px;
            font-weight: 700;
        }
        .logout-btn {
            background: white;
            color: #dc2626;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }
        .manage-user-form {
            display: none;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-top: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #374151;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .user-table th, .user-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .user-table th {
            background: #f3f4f6;
            font-weight: 600;
        }
        .btn-delete {
            background: #dc2626;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-delete:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <div>
                <span style="margin-right: 15px; color: white;">{{ session('user')['name'] }}</span>
                <button class="logout-btn" onclick="logout()">Logout</button>
            </div>
        </div>
        
        <div class="admin-nav">
            <a href="#" onclick="toggleManageUser()">Manage Users</a>
            <a href="/admin/products">Products</a>
            <a href="/admin/orders">Orders</a>
            <a href="/admin/settings">Settings</a>
        </div>
        
        <!-- Form Manage User -->
        <div id="manageUserForm" class="manage-user-form">
            <h3>Buat Akun Baru</h3>
            <form id="createAccountForm">
                @csrf
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="Manager">Manager</option>
                        <option value="Sales">Sales</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary" id="submitBtn">Buat Akun</button>
            </form>
        </div>

        <!-- Tabel User -->
        <div class="admin-card">
            <h3>Daftar User</h3>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <!-- Data user akan diisi melalui JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Pastikan token CSRF dan Authorization tersedia untuk semua request AJAX
        const token = '{{ session("jwt_token") }}';
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': `Bearer ${token}`
            }
        });

        function toggleManageUser() {
            const form = document.getElementById('manageUserForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        async function logout() {
            try {
                await $.post('/logout');
                window.location.replace('/login');
            } catch (error) {
                console.error('Logout error:', error);
                alert('Gagal logout. Silakan coba lagi.');
            }
        }

        async function loadUsers() {
            const tbody = $('#userTableBody');
            
            try {
                // Tampilkan loading state
                tbody.html('<tr><td colspan="4" style="text-align: center;">Loading...</td></tr>');
                
                console.log('Fetching users list...');
                const response = await $.ajax({
                    url: '/admin/users',
                    method: 'GET'
                });
                
                console.log('Users list received:', response);
                
                tbody.empty();
                
                if (!Array.isArray(response)) {
                    throw new Error('Invalid response format');
                }
                
                if (response.length === 0) {
                    tbody.html('<tr><td colspan="4" style="text-align: center;">Tidak ada user</td></tr>');
                    return;
                }
                
                response.forEach(user => {
                    // Skip admin dari list
                    if (user.role.toLowerCase() === 'admin') return;
                    
                    tbody.append(`
                        <tr>
                            <td>${user.name || '-'}</td>
                            <td>${user.email || '-'}</td>
                            <td>${user.role || '-'}</td>
                            <td>
                                <button 
                                    onclick="deleteUser(${user.id})"
                                    class="btn-delete"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    `);
                });
            } catch (error) {
                console.error('Error loading users:', error);
                const errorMessage = error.responseJSON?.message || error.message || 'Gagal memuat daftar user';
                tbody.html(`<tr><td colspan="4" style="text-align: center; color: #dc2626;">${errorMessage}</td></tr>`);
                
                // Jika unauthorized, redirect ke login
                if (error.status === 401) {
                    alert('Sesi Anda telah berakhir. Silakan login kembali.');
                    window.location.replace('/login');
                }
            }
        }

        async function deleteUser(id) {
            if (!confirm('Anda yakin ingin menghapus user ini?')) return;
            
            try {
                await $.ajax({
                    url: `/admin/users/${id}`,
                    method: 'DELETE'
                });
                await loadUsers();
            } catch (error) {
                console.error('Error deleting user:', error);
                alert('Gagal menghapus user. Silakan coba lagi.');
            }
        }

        $(document).ready(function() {
            loadUsers();

            $('#createAccountForm').on('submit', async function(e) {
                e.preventDefault();
                const submitBtn = $('#submitBtn');
                submitBtn.prop('disabled', true).text('Memproses...');

                try {
                    const formData = {
                        name: this.elements['name'].value,
                        email: this.elements['email'].value,
                        password: this.elements['password'].value,
                        role: this.elements['role'].value
                    };

                    console.log('Sending create account request:', {
                        ...formData,
                        password: '***' // Hide password in logs
                    });

                    const response = await $.ajax({
                        url: '/admin/create-account',
                        method: 'POST',
                        data: JSON.stringify(formData),
                        contentType: 'application/json'
                    });

                    console.log('Create account response:', response);
                    
                    if (response.status === 'success') {
                        this.reset();
                        await loadUsers();
                        alert('Akun berhasil dibuat!');
                    } else {
                        throw new Error(response.message || 'Gagal membuat akun');
                    }
                } catch (error) {
                    console.error('Error creating account:', error);
                    let errorMessage = 'Gagal membuat akun. ';
                    
                    if (error.responseJSON) {
                        errorMessage += error.responseJSON.message || '';
                    } else if (error.status === 0) {
                        errorMessage += 'Tidak dapat terhubung ke server.';
                    } else if (error.status === 401) {
                        errorMessage += 'Sesi Anda telah berakhir. Silakan login kembali.';
                        setTimeout(() => window.location.replace('/login'), 1500);
                    } else {
                        errorMessage += error.message || 'Silakan coba lagi.';
                    }
                    
                    alert(errorMessage);
                } finally {
                    submitBtn.prop('disabled', false).text('Buat Akun');
                }
            });
        });
    </script>
</body>
</html>
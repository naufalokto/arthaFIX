@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-blue-600 text-white">
        <div class="p-4">
            <h2 class="text-xl font-bold mb-4">{{ session('user')['name'] }}</h2>
            <nav>
                <ul class="space-y-2">
                    <li>
                        <a href="#" onclick="showSection('dashboard')" class="flex items-center p-2 hover:bg-blue-700 rounded">
                            <i class="fas fa-box mr-2"></i>
                            <span>Produk</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="showSection('cart')" class="flex items-center p-2 hover:bg-blue-700 rounded">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            <span>Keranjang</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="showSection('transactions')" class="flex items-center p-2 hover:bg-blue-700 rounded">
                            <i class="fas fa-history mr-2"></i>
                            <span>Transaksi</span>
                        </a>
                    </li>
                    <li>
                        <a href="/api/logout" class="flex items-center p-2 bg-red-500 hover:bg-red-600 rounded">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <!-- Dashboard/Products Section -->
        <section id="dashboard-section">
            <h2 class="text-2xl font-bold mb-4">Produk Tersedia</h2>
            <div id="loading-products" class="text-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-600">Memuat produk...</p>
            </div>
            <div id="products-grid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Products will be loaded here -->
            </div>
        </section>

        <!-- Cart Section -->
        <section id="cart-section" class="hidden">
            <div class="mb-6">
                <h2 class="text-2xl font-bold mb-4">Keranjang Belanja</h2>
                <div id="loading-cart" class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-4 text-gray-600">Memuat keranjang...</p>
                </div>
                <div id="empty-cart" class="hidden text-center py-8">
                    <p class="text-gray-600">Keranjang belanja kosong</p>
                    <button onclick="showSection('dashboard')" class="mt-4 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                        Lihat Produk
                    </button>
                </div>
                <div id="cart-content" class="hidden">
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3">Produk</th>
                                    <th class="text-right py-3">Harga</th>
                                    <th class="text-center py-3">Jumlah</th>
                                    <th class="text-right py-3">Subtotal</th>
                                    <th class="text-center py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cart-list">
                                <!-- Cart items will be loaded here -->
                            </tbody>
                            <tfoot class="border-t">
                                <tr>
                                    <td colspan="3" class="py-4 text-right font-bold">Total:</td>
                                    <td class="py-4 text-right font-bold" id="total-amount">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="text-right">
                        <button onclick="processCheckout()" class="bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700">
                            <i class="fas fa-shopping-cart mr-2"></i>Checkout
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Transactions Section -->
        <section id="transactions-section" class="hidden">
            <h2 class="text-2xl font-bold mb-4">Riwayat Transaksi</h2>
            <div id="loading-transactions" class="text-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-600">Memuat transaksi...</p>
            </div>
            <div id="empty-transactions" class="hidden text-center py-8">
                <p class="text-gray-600">Belum ada transaksi</p>
            </div>
            <div id="transactions-content" class="hidden space-y-4">
                <!-- Transaction items will be loaded here -->
            </div>
        </section>
    </main>
</div>

<!-- Transaction Detail Modal -->
<div class="fixed inset-0 bg-black bg-opacity-50 hidden" id="modal-bg">
    <div class="bg-white rounded-lg max-w-2xl mx-auto mt-20 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Detail Transaksi</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="modal-content">
            <!-- Transaction details will be loaded here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentCart = [];
    let currentSection = 'dashboard';

    document.addEventListener('DOMContentLoaded', function() {
        loadProducts();
        showSection('dashboard');
    });

    function showSection(section) {
        document.getElementById('dashboard-section').classList.add('hidden');
        document.getElementById('cart-section').classList.add('hidden');
        document.getElementById('transactions-section').classList.add('hidden');

        document.getElementById(section + '-section').classList.remove('hidden');
        
        if (section === 'dashboard') {
            loadProducts();
        } else if (section === 'cart') {
            loadCartItems();
        } else if (section === 'transactions') {
            loadTransactions();
        }
        
        currentSection = section;
    }

    function loadProducts() {
        const loadingEl = document.getElementById('loading-products');
        const productsGrid = document.getElementById('products-grid');
        
        loadingEl.classList.remove('hidden');
        productsGrid.classList.add('hidden');
        productsGrid.innerHTML = '';

        fetch('/api/products', {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            loadingEl.classList.add('hidden');
            productsGrid.classList.remove('hidden');

            if (data.status === 'success' && Array.isArray(data.products)) {
                data.products.forEach(product => {
                    const card = document.createElement('div');
                    card.className = 'bg-white rounded-lg shadow-md p-6';
                    card.innerHTML = `
                        <h3 class="text-lg font-semibold mb-2">${product.product_name}</h3>
                        <p class="text-gray-600 mb-4">${product.description || 'No description available'}</p>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-lg font-bold">Rp ${product.price.toLocaleString('id-ID')}</span>
                            <span class="text-sm text-gray-500">Stock: ${product.quantity}</span>
                        </div>
                        <button onclick="addToCart(${product.product_id})" 
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition-colors"
                                ${product.quantity < 1 ? 'disabled' : ''}>
                            ${product.quantity < 1 ? 'Stok Habis' : 'Tambah ke Keranjang'}
                        </button>
                    `;
                    productsGrid.appendChild(card);
                });
            } else {
                productsGrid.innerHTML = '<p class="text-center text-gray-600">Tidak ada produk tersedia</p>';
            }
        })
        .catch(err => {
            console.error('Error loading products:', err);
            loadingEl.classList.add('hidden');
            productsGrid.classList.remove('hidden');
            productsGrid.innerHTML = '<p class="text-center text-red-600">Gagal memuat produk</p>';
        });
    }

    function addToCart(productId) {
        const token = "{{ session('jwt_token') }}";
        if (!token) {
            alert('Silakan login terlebih dahulu!');
            window.location.href = '/login';
            return;
        }

        fetch('/customer/cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Produk berhasil ditambahkan ke keranjang');
                showSection('cart');
            } else {
                alert(data.message || 'Gagal menambahkan produk ke keranjang');
            }
        })
        .catch(err => {
            console.error('Error adding to cart:', err);
            alert('Terjadi kesalahan saat menambahkan ke keranjang');
        });
    }

    // Load cart items
    function loadCartItems() {
        const loadingEl = document.getElementById('loading-cart');
        const cartContentEl = document.getElementById('cart-content');
        const emptyCartEl = document.getElementById('empty-cart');
        const cartListEl = document.getElementById('cart-list');
        
        loadingEl.classList.remove('hidden');
        cartContentEl.classList.add('hidden');
        emptyCartEl.classList.add('hidden');
        cartListEl.innerHTML = '';

        const token = "{{ session('jwt_token') }}";
        if (!token) {
            alert('Silakan login terlebih dahulu!');
            window.location.href = '/login';
            return;
        }

        fetch('/customer/cart', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            loadingEl.classList.add('hidden');
            
            if (data.status === 'success' && Array.isArray(data.cartItems) && data.cartItems.length > 0) {
                currentCart = data.cartItems;
                cartContentEl.classList.remove('hidden');
                
                let totalAmount = 0;
                
                data.cartItems.forEach(item => {
                    const subtotal = item.quantity * item.product.price;
                    totalAmount += subtotal;
                    
                    const tr = document.createElement('tr');
                    tr.className = 'border-b';
                    tr.innerHTML = `
                        <td class="py-4">${item.product.product_name}</td>
                        <td class="py-4 text-right">Rp ${item.product.price.toLocaleString('id-ID')}</td>
                        <td class="py-4 text-center">${item.quantity}</td>
                        <td class="py-4 text-right">Rp ${subtotal.toLocaleString('id-ID')}</td>
                        <td class="py-4 text-center">
                            <button onclick="removeFromCart([${item.cart_id}])"
                                    class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    cartListEl.appendChild(tr);
                });
                
                document.getElementById('total-amount').textContent = `Rp ${totalAmount.toLocaleString('id-ID')}`;
            } else {
                emptyCartEl.classList.remove('hidden');
            }
        })
        .catch(err => {
            console.error('Error loading cart:', err);
            loadingEl.classList.add('hidden');
            emptyCartEl.classList.remove('hidden');
        });
    }

    // Remove from cart
    function removeFromCart(cartIds) {
        if (!confirm('Hapus item dari keranjang?')) return;

        const token = "{{ session('jwt_token') }}";
        if (!token) {
            alert('Silakan login terlebih dahulu!');
            window.location.href = '/login';
            return;
        }

        fetch('/customer/cart', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ cart_ids: cartIds })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                loadCartItems();
            } else {
                alert(data.message || 'Gagal menghapus item dari keranjang');
            }
        })
        .catch(err => {
            console.error('Error removing from cart:', err);
            alert('Terjadi kesalahan saat menghapus item');
        });
    }

    // Process checkout
    function processCheckout() {
        if (currentCart.length === 0) {
            alert('Keranjang kosong!');
            return;
        }

        const token = "{{ session('jwt_token') }}";
        if (!token) {
            alert('Silakan login terlebih dahulu!');
            window.location.href = '/login';
            return;
        }

        const items = currentCart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            price: item.product.price,
            name: item.product.product_name
        }));

        fetch('/customer/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ items: items })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.token) {
                snap.pay(data.token, {
                    onSuccess: function(result) {
                        alert('Pembayaran berhasil!');
                        loadCartItems();
                        showSection('transactions');
                    },
                    onPending: function(result) {
                        alert('Pembayaran menunggu konfirmasi.');
                        showSection('transactions');
                    },
                    onError: function(result) {
                        alert('Pembayaran gagal!');
                    },
                    onClose: function() {
                        console.log('Payment popup closed');
                    }
                });
            } else {
                alert(data.message || 'Gagal memproses checkout');
            }
        })
        .catch(err => {
            console.error('Error processing checkout:', err);
            alert('Terjadi kesalahan saat memproses pembayaran');
        });
    }

    // Load transactions
    function loadTransactions() {
        const loadingEl = document.getElementById('loading-transactions');
        const contentEl = document.getElementById('transactions-content');
        const emptyEl = document.getElementById('empty-transactions');
        
        loadingEl.classList.remove('hidden');
        contentEl.classList.add('hidden');
        emptyEl.classList.add('hidden');

        const token = "{{ session('jwt_token') }}";
        if (!token) {
            showAlert('error', 'Silakan login terlebih dahulu!');
            window.location.href = '/login';
            return;
        }

        fetch('/customer/transactions/summary', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            loadingEl.classList.add('hidden');
            
            if (data.status === 'success' && Array.isArray(data.transactions) && data.transactions.length > 0) {
                contentEl.classList.remove('hidden');
                contentEl.innerHTML = data.transactions.map(transaction => `
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h4 class="font-semibold">Transaksi #${transaction.transaction_id}</h4>
                                <p class="text-sm text-gray-500">${formatDate(transaction.date)}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm ${getStatusColor(transaction.status)}">
                                ${transaction.status}
                            </span>
                        </div>
                        <p class="text-gray-600 mb-4">${transaction.items_summary}</p>
                        <div class="flex justify-between items-center">
                            <span class="font-bold">Total: Rp ${transaction.total_price.toLocaleString('id-ID')}</span>
                            <button onclick="showTransactionDetail(${transaction.transaction_id})" 
                                    class="text-blue-600 hover:text-blue-800 flex items-center">
                                <span>Lihat Detail</span>
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                `).join('');
            } else {
                emptyEl.classList.remove('hidden');
                emptyEl.innerHTML = `
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="mt-4 text-gray-600">Belum ada transaksi</p>
                        <p class="text-sm text-gray-500">Mulai berbelanja untuk melihat riwayat transaksi Anda</p>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Error loading transactions:', err);
            loadingEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');
            emptyEl.innerHTML = `
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-4 text-red-600">Gagal memuat transaksi</p>
                    <p class="text-sm text-gray-500 mt-2">Silakan coba lagi nanti atau hubungi customer service</p>
                    <button onclick="loadTransactions()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        Coba Lagi
                    </button>
                </div>
            `;
            showAlert('error', 'Gagal memuat transaksi. Silakan coba lagi.');
        });
    }

    // View transaction detail
    function showTransactionDetail(transactionId) {
        const modalBg = document.getElementById('modal-bg');
        const modalContent = document.getElementById('modal-content');
        
        modalBg.classList.remove('hidden');
        modalContent.innerHTML = `
            <div class="flex items-center justify-center p-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="ml-2 text-gray-600">Memuat detail transaksi...</span>
            </div>
        `;

        const token = "{{ session('jwt_token') }}";
        fetch(`/api/customer/transactions/detail?id=${transactionId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = '/login';
                    return;
                }
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success' && data.data) {
                const detail = data.data;
                const modalContent = `
                    <div class="transaction-detail">
                        <h3>${detail.product.product_name}</h3>
                        <p class="text-gray-600">${detail.product.note}</p>
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <p class="font-semibold">Jumlah:</p>
                                <p>${detail.quantity} unit</p>
                            </div>
                            <div>
                                <p class="font-semibold">Harga Satuan:</p>
                                <p>Rp ${formatPrice(detail.price)}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Total:</p>
                                <p>Rp ${formatPrice(detail.price * detail.quantity)}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Tanggal:</p>
                                <p>${formatDate(detail.date_time)}</p>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('modal-content').innerHTML = modalContent;
                document.getElementById('modal-bg').classList.remove('hidden');
            } else {
                showAlert('error', 'Gagal memuat detail transaksi');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Gagal memuat detail transaksi');
        });
    }

    // Helper function for transaction status styling
    function getStatusColor(status) {
        switch (status.toLowerCase()) {
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    // Close modal
    function closeModal() {
        document.getElementById('modal-bg').classList.add('hidden');
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('id-ID').format(price);
    }

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-4 right-4 p-4 rounded-lg ${
            type === 'error' ? 'bg-red-500' : 'bg-green-500'
        } text-white`;
        alertDiv.textContent = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000);
    }
</script>
@endpush
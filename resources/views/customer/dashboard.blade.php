<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Dashboard</title>
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
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Welcome, {{ session('user')['name'] }}</h1>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>

        <div class="admin-nav">
            <a href="/customer/dashboard">Dashboard</a>
            <a href="/customer/transactions">Transactions</a>
            <a href="/customer/cart">Cart</a>
        </div>

        <div class="admin-card">
            <h3>My Cart</h3>
            <button class="btn btn-primary" id="getCartBtn">Get Cart</button>
            <ul id="cartList" class="list-group mt-3">
                <!-- Cart items will be populated here -->
            </ul>
            <div class="mt-3">
                <input type="number" id="productId" placeholder="Product ID" class="form-control mb-2" />
                <input type="number" id="quantity" placeholder="Quantity" class="form-control mb-2" />
                <button class="btn btn-success" id="addToCartBtn">Add to Cart</button>
            </div>
        </div>

        <div class="admin-card">
            <h3>Recent Transactions</h3>
            <button class="btn btn-info" id="viewTransactionsBtn">View Transactions</button>
            <ul id="transactionsList" class="list-group mt-3">
                <!-- Transaction history will be populated here -->
            </ul>
        </div>

        <div class="admin-card">
            <h3>Checkout</h3>
            <button class="btn btn-success" id="checkoutBtn">Proceed to Checkout</button>
        </div>
    </div>

    <script>
        function logout() {
            sessionStorage.removeItem('jwt_token');
            window.location.href = '/login';
        }

        // Fetch User Cart
        document.getElementById('getCartBtn').addEventListener('click', function() {
            fetch('/customer/cart')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        let cartItems = data.cart;
                        let cartList = document.getElementById('cartList');
                        cartList.innerHTML = ''; // Clear the previous cart items
                        cartItems.forEach(item => {
                            let li = document.createElement('li');
                            li.classList.add('list-group-item');
                            li.textContent = `Product: ${item.product_name}, Quantity: ${item.quantity}`;
                            cartList.appendChild(li);
                        });
                    } else {
                        alert('Failed to fetch cart items');
                    }
                })
                .catch(err => alert('Error: ' + err));
        });

        // View Transactions
        document.getElementById('viewTransactionsBtn').addEventListener('click', function() {
    fetch('/customer/transactions')  // Memanggil Laravel API untuk mendapatkan transaksi
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                let transactions = data.transactions;  // Data transaksi yang dikirim dari backend
                let transactionsList = document.getElementById('transactionsList');
                transactionsList.innerHTML = '';  // Menghapus daftar transaksi sebelumnya

                // Loop untuk menampilkan transaksi
                transactions.forEach(transaction => {
                    let li = document.createElement('li');
                    li.classList.add('list-group-item');
                    li.textContent = `Transaction ID: ${transaction.transaction_id}, 
                                      Total: Rp${transaction.total_price.toLocaleString()}, 
                                      Date: ${new Date(transaction.date).toLocaleDateString()}, 
                                      Status: ${transaction.status}`;
                    transactionsList.appendChild(li);
                });
            } else {
                alert('Failed to fetch transactions');  // Menampilkan pesan jika gagal mengambil data
            }
        })
        .catch(err => alert('Error: ' + err));  // Menangani error pada request
});


        // Add Product to Cart
        document.getElementById('addToCartBtn').addEventListener('click', function() {
            let productId = document.getElementById('productId').value;
            let quantity = document.getElementById('quantity').value;

            if (!productId || !quantity) {
                alert('Please fill both product ID and quantity');
                return;
            }

            fetch('/customer/cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Product added to cart');
                } else {
                    alert('Failed to add product to cart');
                }
            })
            .catch(err => alert('Error: ' + err));
        });

        // Checkout
        document.getElementById('checkoutBtn').addEventListener('click', function() {
            fetch('/customer/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${sessionStorage.getItem('jwt_token')}`
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Checkout successful!');
                } else {
                    alert('Checkout failed');
                }
            })
            .catch(err => alert('Error: ' + err));
        });
    </script>
</body>
</html>
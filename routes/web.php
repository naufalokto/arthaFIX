<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/products', function () {
    return view('public.products');
})->name('products');

Route::get('/signup', function () {
    return view('signup');
})->name('signup');

Route::get('/login', function () {
    return view('login');
})->name('login');

// Products API routes (public)
Route::get('/api/products', [ApiController::class, 'getProducts'])->name('products.list');
Route::get('/api/stocks', [ApiController::class, 'getProducts'])->name('stocks.list');

// Customer API routes
Route::prefix('api/customer')->group(function () {
    Route::get('/transactions/detail', [ApiController::class, 'viewTransactionDetailByID']);
    Route::get('/transactions/summary', [ApiController::class, 'viewTransactionSummary']);
    Route::get('/cart', [ApiController::class, 'getUserCart']);
    Route::post('/cart', [ApiController::class, 'addToCart']);
    Route::delete('/cart', [ApiController::class, 'deleteCartItems']);
});

// Payment callback route
Route::get('/payment/callback', function () {
    // Just redirect to customer dashboard - the frontend JS will handle the callback parameters
    return redirect('/customer/dashboard');
})->name('payment.callback');

// Authentication routes
Route::post('/signup', [ApiController::class, 'signup'])->name('auth.signup');
Route::post('/login', [ApiController::class, 'login'])->name('auth.login');
Route::post('/logout', [ApiController::class, 'logout'])->name('auth.logout');
Route::get('/logout', [ApiController::class, 'logout'])->name('auth.logout.get');

// Add this route for products
Route::get('/products', function () {
    return view('products');
})->name('products');

// Protected routes
Route::prefix(['auth.check'])->group(function () {
    Route::post('/logout', [ApiController::class, 'logout'])->name('auth.logout');
    
    // Admin routes
    // Route::prefix(['admin'])->prefix('admin')->group(function () {
    //     Route::get('/dashboard', function () {
    //         return view('admin.dashboard');
    //     });
    //     Route::get('/users', [ApiController::class, 'getUsers']);
    //     Route::post('/create-account', [ApiController::class, 'createAccount']);
    //     Route::delete('/users/{id}', [ApiController::class, 'deleteUser']);
    // });
    
// Admin routes
Route::prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/viewuser', [AdminController::class, 'getUsers']);
    Route::post('/create-account', [AdminController::class, 'createAccount']);
    Route::delete('/delete-user', [AdminController::class, 'deleteUser']);

    // Product Management
    Route::get('/products', [AdminController::class, 'products'])->name('admin.products');
    Route::post('/products', [AdminController::class, 'insertProductAndStock']);
    Route::put('/products/{id}/stock', [AdminController::class, 'updateProductStock']);
    Route::delete('/products', [AdminController::class, 'deleteStock']);

    // Raw Materials
    Route::get('/raw-materials', [AdminController::class, 'rawMaterials'])->name('admin.raw-materials');
    Route::post('/raw-materials', [AdminController::class, 'insertRawMaterial']);
    Route::delete('/raw-materials/{id}', [AdminController::class, 'deleteRawMaterial']);

    // Transactions
    Route::get('/transactions', [AdminController::class, 'transactions'])->name('admin.transactions');
    Route::get('/transaction-view', [AdminController::class, 'viewTransactionSummary']);

    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
});

    // Sales routes
    Route::middleware(['sales'])->prefix('sales')->group(function () {
        Route::get('/dashboard', [SalesController::class, 'dashboard'])->name('sales.dashboard');
        Route::get('/stocks', [ApiController::class, 'getStock']);
        Route::get('/products', [ApiController::class, 'getProducts']);
    });
});

// Customer routes
Route::middleware(['auth'])->prefix('customer')->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('customer.dashboard');

    Route::get('/products', [ApiController::class, 'getProducts'])->name('customer.products');
    Route::get('/transactions/summary', [ApiController::class, 'viewTransactionSummary'])->name('customer.transactions.summary');
    Route::get('/transactions/detail', [ApiController::class, 'viewTransactionDetailByID'])->name('customer.transactions.detail');
    Route::post('/checkout', [ApiController::class, 'checkout'])->name('customer.checkout');
    Route::post('/cart', [ApiController::class, 'addToCart'])->name('customer.cart.add');
    Route::get('/cart', [ApiController::class, 'getUserCart'])->name('customer.cart.index');
    Route::delete('/cart', [ApiController::class, 'deleteCartItems'])->name('customer.cart.delete');
});

// Debug route for Midtrans configuration
Route::get('/debug/midtrans-config', [DebugController::class, 'midtransConfig']);

// Test route for Midtrans configuration
Route::get('/test-midtrans', function () {
    return view('test-midtrans');
});
// Other routes
Route::post('/midtrans/webhook', [ApiController::class, 'midtransWebhook']);

// Manager Routes
Route::prefix('manager')->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');
});

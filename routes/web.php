<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/signup', function () {
    return view('signup');
})->name('signup');

Route::get('/login', function () {
    return view('login');
})->name('login');

// Authentication routes
Route::post('/signup', [ApiController::class, 'signup']);
Route::post('/login', [ApiController::class, 'login']);
Route::post('/logout', [ApiController::class, 'logout']);
Route::post('/midtrans/webhook', [ApiController::class, 'midtransWebhook']);

// Add this route for products
Route::get('/products', function () {
    return view('products');
})->name('products');

// Admin routes
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        // Cek session untuk admin
        if (!session('jwt_token') || !session('user') || strtolower(session('user')['role']) !== 'admin') {
            return redirect('/login');
        }
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    Route::post('/create-account', function() {
        if (!session('jwt_token') || !session('user') || strtolower(session('user')['role']) !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return app(ApiController::class)->createAccount(request());
    })->name('admin.create-account');
    
    Route::get('/users', function() {
        if (!session('jwt_token') || !session('user') || strtolower(session('user')['role']) !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return app(ApiController::class)->getUsers();
    })->name('admin.users.index');
    
    Route::delete('/users/{id}', function($id) {
        if (!session('jwt_token') || !session('user') || strtolower(session('user')['role']) !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return app(ApiController::class)->deleteUser($id);
    })->name('admin.users.delete');
});

// Manager routes
Route::prefix('manager')->group(function () {
    Route::get('/dashboard', function () {
        if (!session('jwt_token') || !session('user') || strtolower(session('user')['role']) !== 'manager') {
            return redirect('/login');
        }
        return view('manager.dashboard');
    })->name('manager.dashboard');
    
    Route::post('/gemini/analyze', [ApiController::class, 'analyzeAllProducts']);
    Route::get('/transaction-view', [ApiController::class, 'salesRecap']);
});

// Sales routes
Route::prefix('sales')->group(function () {
    Route::get('/dashboard', function () {
        if (!session('jwt_token') || !session('user') || strtolower(session('user')['role']) !== 'sales') {
            return redirect('/login');
        }
        return view('sales.dashboard');
    })->name('sales.dashboard');
    
    Route::post('/stocks', [ApiController::class, 'insertProductAndStock']);
    Route::put('/stocks', [ApiController::class, 'updateProductStock']);
    Route::post('/rawmaterial', [ApiController::class, 'insertRawMaterial']);
    Route::get('/rawmaterial', [ApiController::class, 'getRawMaterialsSorted']);
});

// Customer routes
Route::prefix('customer')->group(function () {
    Route::get('/', function () {
        if (!session('jwt_token') || !session('user') || strtolower(session('user')['role']) !== 'customer') {
            return redirect('/login');
        }
        return view('customer.dashboard');
    })->name('customer.dashboard');
    
    Route::get('/transactions', [ApiController::class, 'viewTransaction']);
    Route::post('/checkout', [ApiController::class, 'checkout']);
    Route::post('/cart', [ApiController::class, 'addToCart']);
    Route::get('/cart', [ApiController::class, 'getUserCart']);
    Route::delete('/cart', [ApiController::class, 'deleteCartItems']);
});

Route::fallback(function () {
    return redirect()->route('home');
});
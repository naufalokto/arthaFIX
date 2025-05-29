<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{

    private $api = 'http://localhost:9090';

    public function signup(Request $request)
{
    $client = new \GuzzleHttp\Client([
        'base_uri' => 'http://localhost:9090/',
        'timeout' => 10,
        'verify' => false
    ]);
    
    try {
        $response = $client->post('signup', [
            'json' => [
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'username' => $request->username,
                'password' => $request->password
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);
        
        return response()->json(
            json_decode($response->getBody()->getContents()),
            $response->getStatusCode()
        );
        
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal terhubung ke API Go',
            'error' => $e->getMessage(),
            'detail' => 'Pastikan endpoint /signup tersedia di API Go'
        ], 500);
    }
}
public function login(Request $request)
{
    try {
        // Log request data
        \Log::info('Login attempt received', [
            'email' => $request->email,
            'request_data' => $request->all()
        ]);
        
        // Validasi input
        if (!$request->email || !$request->password) {
            \Log::warning('Login attempt failed: Missing credentials');
            return response()->json([
                'status' => 'error',
                'message' => 'Email dan password harus diisi'
            ], 422);
        }
        
        // Buat HTTP client
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->api,
            'timeout' => 30,
            'verify' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

        \Log::info('Sending request to backend', [
            'url' => $this->api . '/login'
        ]);

        // Kirim request ke backend Go
        $response = $client->post('/login', [
            'json' => [
                'email' => $request->email,
                'password' => $request->password
            ]
        ]);
        
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);
        
        \Log::info('Login response received', [
            'status_code' => $response->getStatusCode(),
            'response_data' => $data
        ]);

        if ($response->getStatusCode() === 200 && isset($data['token'])) {
            // Simpan data user ke session
            session([
                'user' => [
                    'email' => $data['email'],
                    'name' => $data['name'],
                    'role' => $data['role']
                ],
                'jwt_token' => $data['token']
            ]);

            \Log::info('Login successful', [
                'user_role' => $data['role'],
                'session_data' => session()->all()
            ]);

            // Tentukan redirect berdasarkan role
            $redirectUrl = match(strtolower($data['role'])) {
                'admin' => '/admin/dashboard',
                'manager' => '/manager/dashboard',
                'sales' => '/sales/dashboard',
                default => '/'
            };

            \Log::info('Redirecting user', [
                'role' => $data['role'],
                'redirect_url' => $redirectUrl
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil',
                'redirect' => $redirectUrl,
                'user' => [
                    'email' => $data['email'],
                    'name' => $data['name'],
                    'role' => $data['role']
                ]
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => $data['message'] ?? 'Login gagal'
        ], 401);

    } catch (\GuzzleHttp\Exception\ConnectException $e) {
        \Log::error('Backend connection error', [
            'message' => $e->getMessage()
        ]);
        return response()->json([
            'status' => 'error',
            'message' => 'Tidak dapat terhubung ke server'
        ], 503);
    } catch (\Exception $e) {
        \Log::error('Login error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan pada server'
        ], 500);
    }
}
    public function midtransWebhook(Request $request)
    {
        // Implementasi webhook midtrans
    }

    // ADMIN
    public function createAccount(Request $request)
    {
        try {
            // Log request data (tanpa password)
            $requestData = $request->all();
            $logData = array_merge($requestData, ['password' => '***']);
            
            \Log::info('Create account attempt received', [
                'request_data' => $logData,
                'api_url' => $this->api . '/admin/create-account',
                'headers' => [
                    'Authorization' => request()->header('Authorization'),
                    'Content-Type' => request()->header('Content-Type')
                ]
            ]);

            // Validasi input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:6',
                'role' => 'required|in:Manager,Sales'
            ]);

            if ($validator->fails()) {
                \Log::warning('Create account validation failed', [
                    'errors' => $validator->errors()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal: ' . implode(', ', $validator->errors()->all())
                ], 422);
            }

            // Split name into firstname and lastname
            $nameParts = explode(' ', $request->name, 2);
            $firstname = $nameParts[0];
            $lastname = isset($nameParts[1]) ? $nameParts[1] : '';

            // Generate username from email
            $username = explode('@', $request->email)[0];

            // Format data sesuai ekspektasi backend Go
            $userData = [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $request->email,
                'username' => $username,
                'password' => $request->password,
                'role' => ucfirst($request->role)
            ];

            // Log data yang akan dikirim (tanpa password)
            \Log::info('Data yang akan dikirim ke backend:', array_merge(
                $userData,
                ['password' => '***']
            ));

            // Ambil token dari header
            $authHeader = request()->header('Authorization');
            if (!$authHeader) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Authorization header tidak ditemukan'
                ], 401);
            }

            // Buat HTTP client dengan konfigurasi yang benar
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->api,
                'timeout' => 30,
                'verify' => false
            ]);

            \Log::info('Sending create account request to backend', [
                'url' => $this->api . '/admin/create-account',
                'method' => 'POST',
                'data' => array_merge($userData, ['password' => '***']),
                'headers_sent' => [
                    'Authorization' => $authHeader,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            // Kirim request menggunakan Guzzle
            $response = $client->post('/admin/create-account', [
                'headers' => [
                    'Authorization' => $authHeader,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => $userData
            ]);
            
            // Get response body
            $body = $response->getBody()->getContents();
            
            // Log raw response
            \Log::info('Backend response received', [
                'status' => $response->getStatusCode(),
                'body' => $body,
                'headers' => $response->getHeaders()
            ]);

            // Parse JSON response
            $data = json_decode($body, true);

            if ($response->getStatusCode() === 201 || $response->getStatusCode() === 200) {
                \Log::info('Account creation successful', [
                    'email' => $request->email,
                    'role' => $request->role
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Akun berhasil dibuat',
                    'user' => $data['user'] ?? null
                ], 201);
            }

            // Jika sampai sini berarti ada error dari backend
            throw new \Exception($data['message'] ?? 'Gagal membuat akun');

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $response = $e->getResponse();
            $body = $response->getBody()->getContents();
            
            \Log::error('Backend server error', [
                'status' => $response->getStatusCode(),
                'body' => $body,
                'request' => [
                    'method' => $e->getRequest()->getMethod(),
                    'url' => (string) $e->getRequest()->getUri(),
                    'headers' => $e->getRequest()->getHeaders(),
                    'body' => json_decode($e->getRequest()->getBody()->getContents(), true)
                ]
            ]);
            
            $errorMessage = $body;
            try {
                $jsonError = json_decode($body, true);
                if ($jsonError && isset($jsonError['message'])) {
                    $errorMessage = $jsonError['message'];
                }
            } catch (\Exception $jsonEx) {
                // Jika gagal parse JSON, gunakan pesan error mentah
            }
            
            // Cek jika error karena duplicate username
            if (strpos($body, 'user_username_key') !== false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Username sudah digunakan. Silakan gunakan email lain.'
                ], 400);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat akun: ' . $errorMessage
            ], 500);
            
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = json_decode($response->getBody()->getContents(), true);
            
            \Log::error('Backend client error', [
                'status' => $response->getStatusCode(),
                'body' => $body,
                'request' => [
                    'method' => $e->getRequest()->getMethod(),
                    'url' => (string) $e->getRequest()->getUri(),
                    'headers' => $e->getRequest()->getHeaders(),
                    'body' => json_decode($e->getRequest()->getBody()->getContents(), true)
                ]
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $body['message'] ?? 'Gagal membuat akun'
            ], $response->getStatusCode());
            
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            \Log::error('Backend connection error', [
                'message' => $e->getMessage(),
                'request' => [
                    'url' => $this->api . '/admin/create-account'
                ]
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat terhubung ke server. Pastikan backend berjalan.'
            ], 503);
            
        } catch (\Exception $e) {
            \Log::error('Create account error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat akun: ' . $e->getMessage()
            ], 500);
        }
    }

    // SALES
    public function insertProductAndStock(Request $request)
    {
        try {
            \Log::info('Insert product and stock attempt received', [
                'request_data' => $request->all()
            ]);

            // Validasi input
            $validator = Validator::make($request->all(), [
                'product.product_name' => 'required|string',
                'product.price' => 'required|numeric|min:0',
                'product.note' => 'nullable|string',
                'stock.stock' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal: ' . implode(', ', $validator->errors()->all())
                ], 422);
            }

            // Buat HTTP client
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->api,
                'timeout' => 30,
                'verify' => false
            ]);

            // Kirim request ke backend
            $response = $client->post('/sales/stocks', [
                'headers' => [
                    'Authorization' => request()->header('Authorization'),
                    'Content-Type' => 'application/json'
                ],
                'json' => $request->all()
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return response()->json($data, $response->getStatusCode());

        } catch (\Exception $e) {
            \Log::error('Failed to insert product and stock', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan produk dan stok: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProductStock(Request $request)
    {
        try {
            \Log::info('Update product stock attempt received', [
                'request_data' => $request->all()
            ]);

            // Validasi input
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|integer',
                'stock' => 'required|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal: ' . implode(', ', $validator->errors()->all())
                ], 422);
            }

            // Buat HTTP client
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->api,
                'timeout' => 30,
                'verify' => false
            ]);

            // Kirim request ke backend
            $response = $client->put('/sales/stocks', [
                'headers' => [
                    'Authorization' => request()->header('Authorization'),
                    'Content-Type' => 'application/json'
                ],
                'json' => $request->all()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Stok berhasil diperbarui'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Failed to update product stock', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui stok: ' . $e->getMessage()
            ], 500);
        }
    }

    public function insertRawMaterial(Request $request)
    {
        try {
            \Log::info('Insert raw material attempt received', [
                'request_data' => $request->all()
            ]);

            // Validasi input
            $validator = Validator::make($request->all(), [
                'raw_material_name' => 'required|string',
                'price' => 'required|numeric|min:0',
                'supplier' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal: ' . implode(', ', $validator->errors()->all())
                ], 422);
            }

            // Buat HTTP client
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->api,
                'timeout' => 30,
                'verify' => false
            ]);

            // Kirim request ke backend
            $response = $client->post('/sales/rawmaterial', [
                'headers' => [
                    'Authorization' => request()->header('Authorization'),
                    'Content-Type' => 'application/json'
                ],
                'json' => $request->all()
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return response()->json($data, $response->getStatusCode());

        } catch (\Exception $e) {
            \Log::error('Failed to insert raw material', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan bahan baku: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRawMaterialsSorted(Request $request)
    {
        try {
            \Log::info('Get sorted raw materials attempt received');

            // Buat HTTP client
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->api,
                'timeout' => 30,
                'verify' => false
            ]);

            // Kirim request ke backend
            $response = $client->get('/sales/rawmaterial', [
                'headers' => [
                    'Authorization' => request()->header('Authorization'),
                    'Content-Type' => 'application/json'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return response()->json($data, $response->getStatusCode());

        } catch (\Exception $e) {
            \Log::error('Failed to get raw materials', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data bahan baku: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStock(Request $request)
    {
        try {
            \Log::info('Get stock attempt received');

            // Buat HTTP client
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->api,
                'timeout' => 30,
                'verify' => false
            ]);

            // Kirim request ke backend
            $response = $client->get('/sales/stocks', [
                'headers' => [
                    'Authorization' => request()->header('Authorization'),
                    'Content-Type' => 'application/json'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return response()->json($data, $response->getStatusCode());

        } catch (\Exception $e) {
            \Log::error('Failed to get stock', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data stok: ' . $e->getMessage()
            ], 500);
        }
    }

    // MANAGER
    public function analyzeAllProducts(Request $request)
    {
        // Implementasi analisa produk
    }

    public function salesRecap(Request $request)
    {
        // Implementasi sales recap
    }

    // CUSTOMER
    public function viewTransaction(Request $request)
    {
        // Implementasi view transaksi customer
    }

    public function checkout(Request $request)
    {
        // Implementasi checkout
    }

    public function addToCart(Request $request)
    {
        // Implementasi tambah ke cart
    }

    public function getUserCart(Request $request)
    {
        // Implementasi get user cart
    }

    public function deleteCartItems(Request $request)
    {
        // Implementasi hapus item cart
    }

    public function getUsers()
    {
        try {
            // Log request
            \Log::info('Getting users list');
            
            // Buat HTTP client
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->api,
                'timeout' => 30,
                'verify' => false,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => request()->header('Authorization') // Ambil dari request header
                ]
            ]);

            // Kirim request ke backend
            $response = $client->get('/admin/users');

            $body = $response->getBody()->getContents();
            
            // Log response
            \Log::info('Users list received', [
                'status' => $response->getStatusCode(),
                'body' => $body
            ]);

            // Parse dan return data
            $data = json_decode($body, true);
            
            if ($response->getStatusCode() === 200) {
                return response()->json(
                    $data['users'] ?? $data,
                    200
                );
            }

            throw new \Exception($data['message'] ?? 'Gagal mengambil daftar user');

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = json_decode($response->getBody()->getContents(), true);
            
            \Log::error('Backend client error when getting users', [
                'status' => $response->getStatusCode(),
                'body' => $body,
                'headers' => $e->getRequest()->getHeaders()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $body['message'] ?? 'Gagal mengambil daftar user'
            ], $response->getStatusCode());

        } catch (\Exception $e) {
            \Log::error('Error getting users list', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil daftar user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteUser($id)
    {
        try {
            // Log request
            \Log::info('Deleting user', ['user_id' => $id]);
            
            // Buat HTTP client
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->api,
                'timeout' => 30,
                'verify' => false,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ]);

            // Kirim request ke backend
            $response = $client->delete("/users/{$id}");
            
            // Log response
            \Log::info('User deleted successfully', [
                'user_id' => $id,
                'status' => $response->getStatusCode()
            ]);

            return response()->json([
                'message' => 'User berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Failed to delete user', [
                'user_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Gagal menghapus user'
            ], 500);
        }
    }

    public function logout()
    {
        // Hapus semua data session
        session()->flush();
        
        return redirect('/')->with('message', 'Berhasil logout');
    }
}
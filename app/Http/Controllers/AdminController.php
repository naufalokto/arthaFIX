<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AdminController extends Controller
{
    private function checkAdminAccess()
    {
        if (!session('user') || strtolower(session('user')['role']) !== 'admin') {
            Log::warning('Unauthorized access attempt to admin route', [
                'user' => session('user'),
                'path' => request()->path()
            ]);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }
            
            return redirect('/login')->with('error', 'Unauthorized. Admin access required.');
        }
        
        return null;
    }

    public function dashboard()
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }
        return view('admin.dashboard');
    }

    public function users()
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }
        return view('admin.users');
    }

    public function getUsers()
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }

        try {
            $users = User::where('role', '!=', 'admin')->get();
            return response()->json([
                'status' => 'success',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch users'
            ], 500);
        }
    }

    public function createAccount(Request $request)
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }

        try {
            $validator = \Validator::make($request->all(), [
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'required|string|in:manager,sales'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $response = \Http::post(env('GO_API_URL') . '/admin/create-account', [
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'password' => $request->password,
                'role' => $request->role
            ]);

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating account: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteUser(Request $request)
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }

        try {
            $userId = $request->input('id');
            
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User ID is required'
                ], 400);
            }

            $response = \Http::delete(env('GO_API_URL') . '/admin/users/' . $userId);
            
            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function products()
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }
        return view('admin.products');
    }

    public function insertProductAndStock(Request $request)
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }

        try {
            $validator = \Validator::make($request->all(), [
                'product_name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:0',
                'raw_materials' => 'required|array',
                'raw_materials.*.id' => 'required|integer',
                'raw_materials.*.quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $response = \Http::post(env('GO_API_URL') . '/products', $request->all());

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Product and stock added successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding product and stock: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add product and stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProductStock(Request $request, $id)
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }

        try {
            $validator = \Validator::make($request->all(), [
                'quantity' => 'required|integer|min:0',
                'raw_materials' => 'required|array',
                'raw_materials.*.id' => 'required|integer',
                'raw_materials.*.quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $response = \Http::put(env('GO_API_URL') . '/products/' . $id . '/stock', $request->all());

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Stock updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating stock: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteStock(Request $request)
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }

        try {
            $validator = \Validator::make($request->all(), [
                'id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $response = \Http::delete(env('GO_API_URL') . '/stock/' . $request->id);
            
            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Stock deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting stock: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rawMaterials()
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }
        return view('admin.raw-materials');
    }

    public function insertRawMaterial(Request $request)
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }

        try {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'quantity' => 'required|integer|min:0',
                'unit' => 'required|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $response = \Http::post(env('GO_API_URL') . '/raw-materials', $request->all());

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Raw material added successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding raw material: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add raw material: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteRawMaterial($id)
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }

        try {
            $response = \Http::delete(env('GO_API_URL') . '/raw-materials/' . $id);
            
            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Raw material deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting raw material: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete raw material: ' . $e->getMessage()
            ], 500);
        }
    }

    public function transactions()
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }
        return view('admin.transactions');
    }

    public function viewTransactionSummary(Request $request)
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }

        try {
            $response = \Http::get(env('GO_API_URL') . '/transactions/summary');

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'data' => $response->json()
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting transaction summary: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get transaction summary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reports()
    {
        if ($response = $this->checkAdminAccess()) {
            return $response;
        }
        return view('admin.reports');
    }
} 
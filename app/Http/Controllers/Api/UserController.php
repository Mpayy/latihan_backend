<?php

// php artisan make:controller Api/UserController --api

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseHelper;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();

        return ResponseHelper::success($users, 'Berhasil get user');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8'
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error('Validator Error!!', $validator->errors(), 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password
            ]);

            return ResponseHelper::success($user, 'Registration Success', 201);

        } catch (\Throwable $th) {

            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email,' . $id,
                'password' => 'nullable|min:8'
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error('Validasi Error', '', 422);
            }

            $user = User::findOrFail($id);

            $data = $request->only('name', 'email');

            if ($request->filled('password')) {
                $data['password'] = $request->password;
            }

            $user->update($data);

            return ResponseHelper::success($user, 'Update Data Success');

        } catch (\Throwable $th) {

            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);

            $user->delete();

            return ResponseHelper::success($user, 'Delete User Success');

        } catch (\Throwable $th) {

            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
            
        }
    }
}

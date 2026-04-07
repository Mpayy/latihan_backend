<?php

// php artisan make:controller Api/UserController --api

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseHelper;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::paginate(10);

        return ResponseHelper::success($users, 'Get User Success');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            if(Auth::user()->cannot('create', User::class)){
                return ResponseHelper::error('You do not have access to create this data.', '', 403);
            }
            $validator = Validator::make($request->only('name','username', 'email', 'password'), [
                'name' => 'required|string',
                'username' => 'required|string|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8'
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error('Validator Error!!', $validator->errors(), 422);
            }

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
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
        $user = User::findOrFail($id);

        $post = Post::with('user:id,name,username,profile_photo')
        ->where('user_id', $user->id)
        ->latest()
        ->paginate(10);

        $data = [
            'user' => $user,
            'post' => $post
        ];

        return ResponseHelper::success($data, 'Get User Post Success');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            if(Auth::user()->cannot('update', $user)){
                return ResponseHelper::error('You do not have access to update this data.', '', 403);
            }

            $validator = Validator::make($request->only('name', 'email', 'password'), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email,' . $id,
                'password' => 'nullable|min:8'
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error('Validasi Error', $validator->errors(), 422);
            }

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

            if(Auth::user()->cannot('delete', $user)){
                return ResponseHelper::error('You do not have access to delete this data.', '', 403);
            }

            $user->delete();

            return ResponseHelper::success($user, 'Delete User Success');

        } catch (\Throwable $th) {

            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
            
        }
    }
}

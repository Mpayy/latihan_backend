<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;


class ProfileController extends Controller
{
    public function getProfile()
    {
        $user = Auth::user()->loadCount('posts','followers','following');

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diambil',
            'data' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->only(['name', 'username', 'bio', 'profile_picture']), [
            'name' => 'sometimes|string',
            'username' => 'sometimes|string|unique:users,username,' . $user->id,
            'bio' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $data = $request->only([
            'name',
            'username',
            'bio'
        ]);

        if($request->hasFile('profile_picture')){
           if($user->profile_picture){
            Storage::disk('public')->delete($user->profile_picture);
           }
           $path = $request->file('profile_picture')->store('profile_pictures','public');
           $data['profile_picture'] = $path;
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diupdate',
            'data' => $user
        ]);
    }
}

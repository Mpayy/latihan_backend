<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Post;
use App\Helpers\ResponseHelper;


class ProfileController extends Controller
{
    public function getProfile()
    {
        $user = Auth::user()->loadCount('posts','followers','following');

        return ResponseHelper::success($user, 'Profile successfully retrieved', 200);
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
            return ResponseHelper::error('Validation Error', $validator->errors(), 422);
        }

        $data = $request->only([
            'name',
            'username',
            'bio'
        ]);

        if($request->hasFile('profile_picture')){
           if($user->profile_photo){
            Storage::disk('public')->delete($user->profile_photo);
           }
           $path = $request->file('profile_picture')->store('profile_pictures','public');
           $data['profile_photo'] = $path;
        }

        $user->update($data);
        
        // Reload user with counts to prevent stats disappearing in frontend
        $user = $user->fresh()->loadCount('posts','followers','following');
 
        return ResponseHelper::success($user, 'Profile successfully updated', 200);
    }
    public function getPosts()
    {
        $posts = Post::with('user:id,name,username,profile_photo')
            ->withCount(['likes', 'comments'])
            ->withExists(['likes as is_liked' => function($query) {
                $query->where('likes.user_id', Auth::id());
            }])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return ResponseHelper::success($posts, 'User posts retrieved successfully', 200);
    }
}

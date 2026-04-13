<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query('q');

        if (!$query) {
            return ResponseHelper::success([
                'users' => [],
                'posts' => []
            ], 'Empty query', 200);
        }

        // Search Users
        $users = User::where('name', 'like', "%$query%")
            ->orWhere('username', 'like', "%$query%")
            ->limit(10)
            ->get(['id', 'name', 'username', 'profile_photo', 'bio']);

        // Search Posts
        $posts = Post::with('user:id,name,username,profile_photo')
            ->withCount(['likes', 'comments'])
            ->withExists(['likes as is_liked' => function($q) {
                if (Auth::check()) {
                    $q->where('likes.user_id', Auth::id());
                }
            }])
            ->where('caption', 'like', "%$query%")
            ->latest()
            ->limit(20)
            ->get();

        return ResponseHelper::success([
            'users' => $users,
            'posts' => $posts
        ], 'Search results retrieved', 200);
    }
}

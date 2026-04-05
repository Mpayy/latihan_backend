<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Follow;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    public function index()
    {
        try {
            // $followingIds = Follow::where('follower_id', Auth::id())
            // ->pluck('following_id');
            // $followingIds->push(Auth::id());
            
            $followingIds = Auth::user()->following()->pluck('users.id')->push(Auth::id());
            
            $posts = Post::with('user:id,name,username,profile_photo')
            ->withCount('likes', 'comments')
            ->whereIn('user_id', $followingIds)
            ->latest()
            ->paginate(10);

            return response()->json([
                'message' => 'Feed fetched successfully',
                'data' => $posts
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to fetch feed',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggleLike(Post $post){
        try {
            $userId = Auth::id();

            $alreadyLiked = $post->likedByUsers()->where('user_id', $userId)->exists();

            if($alreadyLiked){
                $post->likedByUsers()->detach($userId);
                $liked = false;
            } else{
                $post->likedByUsers()->attach($userId);
                $liked = true;
            }

            $totalLike = $post->likedByUsers()->count();

            return response()->json([
                'success' => true,
                'liked' => $liked,
                'total_like' => $totalLike,
                'message' => $liked ? 'Post liked successfully' : 'Post unliked successfully',
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle like',
            ], 500);
        }
    }
    
}

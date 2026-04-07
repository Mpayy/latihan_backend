<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;

class LikeController extends Controller
{
    public function toggleLike(Post $post){
        try {
            $userId = Auth::id();

            $alreadyLiked = $post->likes()->where('user_id', $userId)->exists();

            if($alreadyLiked){
                $post->likes()->detach($userId);
                $liked = false;
            } else{
                $post->likes()->attach($userId);
                $liked = true;
            }

            $totalLike = $post->likes()->count();

            $data = [
                'liked' => $liked,
                'total_like' => $totalLike,
            ];

            return ResponseHelper::success($data, $liked ? 'Post liked successfully' : 'Post unliked successfully');

        } catch (\Throwable $th) {
            return ResponseHelper::error('Failed to toggle like', $th->getMessage(), 500);
        }
    }
    
}

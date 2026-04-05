<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class FollowController extends Controller
{
    public function toggleFollow(User $user)
    {
        try {
            $followerId = Auth::id();
            $followingId = $user->id;

            if($followerId === $followingId){
                return ResponseHelper::error('Bad Request', 'You cannot follow yourself', 400);
            }

            $exists = Follow::where('follower_id', $followerId)
            ->where('following_id', $followingId)
            ->exists();

            if($exists){
                Follow::where('follower_id', $followerId)
                ->where('following_id', $followingId)
                ->delete();
                $followed = false;
            }else{
                Follow::create([
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ]);
                $followed = true;
            }

            return ResponseHelper::success([
                'followed' => $followed
            ], $followed ? 'Followed Successfully' : 'Unfollowed Successfully', 200);
            
        } catch (\Throwable $th) {
            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
        }
    }

    public function getFollowers(User $user)
    {
        try {
            $followers = $user->followers()
            ->select('users.id', 'name', 'username', 'bio', 'profile_photo')
            ->paginate(10);
            
            return ResponseHelper::success($followers, 'Daftar Followers', 200);
        } catch (\Throwable $th) {
            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
        }
    }

    public function getFollowing(User $user)
    {
        try {
            $following = $user->following()
            ->select('users.id', 'name', 'username', 'bio', 'profile_photo')
            ->paginate(10);

            return ResponseHelper::success($following, 'Daftar Following', 200);
        } catch (\Throwable $th) {
            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
        }
    }
}

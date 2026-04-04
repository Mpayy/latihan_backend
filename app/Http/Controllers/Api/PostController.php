<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Policies\PostPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with('user:id,name,username,profile_photo')
        ->withCount('likedByUsers as likes_count')
        ->withExists(['likedByUsers as is_liked' => function($query) {
            $query->where('likes.user_id', auth('sanctum')->id() ?? 0);
        }])
        ->latest()->paginate(10);

        return ResponseHelper::success($posts,'Data berhasil diambil',200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'caption' => 'nullable|string|max:1000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            $validator->after(function ($validator) use($request){
                if(!$request->filled('caption') && !$request->hasFile('image')){
                    $validator->errors()->add('post','Caption atau Image harus diisi');
                }
            });

            if($validator->fails()){
                return ResponseHelper::error('Validation Error', $validator->errors(), 422);
            }

            $data = $request->only('caption');

            if($request->hasFile('image')){
               $path = $request->file('image')->store('posts','public');
               $data['image'] = $path;
            }

            $data['user_id'] = Auth::id();

            $post = Post::create($data);

            return ResponseHelper::success($post, 'Post Created Successfully', 201);

            
        } catch (\Throwable $th) {
            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        try {
            $post->load('user:id,name');

            return ResponseHelper::success($post,'Detail Post',200);
        } catch (\Throwable $th) {
            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        try {
            $this->authorize('update', $post);

            $validator = Validator::make($request->all(), [
                'caption' => 'nullable|string|max:1000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            if($validator->fails()){
                return ResponseHelper::error('Validation Error', $validator->errors(), 422);
            }

            $data = [];

            if($request->filled('caption')){
                $data['caption'] = $request->caption;
            }

            if($request->hasFile('image')){
                if($post->image){
                    Storage::disk('public')->delete($post->image);
                }

                $path = $request->file('image')->store('posts','public');
                $data['image'] = $path;
            }

            $post->update($data);
            return ResponseHelper::success($post->fresh(),'Post Updated Successfully',200);
        } catch (\Throwable $th) {
            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        try {
            $this->authorize('delete', $post);

            if($post->image){
                Storage::disk('public')->delete($post->image);
            }

            $post->delete();
            return ResponseHelper::success($post,'Post Deleted Successfully',200);
        } catch (\Throwable $th) {
            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $posts = Post::with('user:id,name,username,profile_photo')
        ->withCount(['likes','comments'])
        ->withExists(['likes as is_liked' => function($query) {
            if(Auth::check()){
                $query->where('likes.user_id', Auth::id());
            }
        }])
        ->latest()
        ->when($request->filled('hashtag'), function ($query) use ($request) {
            $query->where('caption', 'like', '%#'.$request->hashtag.'%');
        })
        ->paginate(10);

        return ResponseHelper::success($posts,'Get Post Success',200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->only('caption', 'image','file'), [
                'caption' => 'nullable|string|max:250',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'file' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,mp4,mov,pdf|max:2048'
            ]);

            $validator->after(function ($validator) use($request){
                if(!$request->filled('caption') && !$request->hasFile('image') && !$request->hasFile('file')){
                    $validator->errors()->add('post','Caption or Image or File must be filled in');
                }
            });

            if($validator->fails()){
                return ResponseHelper::error('Validation Error', $validator->errors(), 422);
            }

            $data = $request->only('caption');
            $basePath = 'posts';

            if($request->hasFile('image')){
               $path = $request->file('image')->store($basePath.'/images','public');
               $data['image'] = $path;
            }

            if($request->hasFile('file')){
               $path = $request->file('file')->store($basePath.'/files','public');
               $data['file'] = $path;
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
            // note: kalau post tidak ketemu, belum ada respon

            $post->load([
                'user:id,name,username,profile_photo',
                'comments.user:id,name,username,profile_photo'
            ])
            ->loadCount(['likes','comments'])
            ->loadExists(['likes as is_liked' => function($query) {
                $query->where('likes.user_id', Auth::id());
            }]);
            // ->findOrFail($post->id);

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
            if(Auth::user()->cannot('update', $post)){
                return ResponseHelper::error('Unauthorized', 'You are not authorized to update this post', 403);
            }

            $validator = Validator::make($request->only('caption', 'image', 'file'), [
                'caption' => 'nullable|string|max:250',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'file' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,mp4,mov,pdf|max:2048',
            ]);

            $validator->after(function ($validator) use($request){
                if(!$request->filled('caption') && !$request->hasFile('image') && !$request->hasFile('file')){
                    $validator->errors()->add('post','Caption or Image or File must be filled in');
                }
            });

            if($validator->fails()){
                return ResponseHelper::error('Validation Error', $validator->errors(), 422);
            }

            $data = [];
            $basePath = 'posts';

            if($request->filled('caption')){
                $data['caption'] = $request->caption;
            }

            if($request->hasFile('image')){
                if($post->image){
                    Storage::disk('public')->delete($post->image);
                }

                $path = $request->file('image')->store($basePath.'/images','public');
                $data['image'] = $path;
            }

            if($request->hasFile('file')){
                if($post->file){
                    Storage::disk('public')->delete($post->file);
                }

                $path = $request->file('file')->store($basePath.'/files','public');
                $data['file'] = $path;
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
            if(Auth::user()->cannot('delete', $post)){
                return ResponseHelper::error('Unauthorized', 'You are not authorized to delete this post', 403);
            }

            if($post->image){
                Storage::disk('public')->delete($post->image);
            }

            if($post->file){
                Storage::disk('public')->delete($post->file);
            }

            $post->delete();
            return ResponseHelper::success($post,'Post Deleted Successfully',200);
        } catch (\Throwable $th) {
            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
        }
    }
}

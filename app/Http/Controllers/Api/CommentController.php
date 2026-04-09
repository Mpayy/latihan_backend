<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        try {
            $validator = Validator::make($request->only('body','image','file'), [
                'body' => 'nullable|string|max:1000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'file' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,mp4,mov,pdf|max:2048',
            ]);

            $validator->after(function ($validator) use($request){
                if(!$request->filled('body') && !$request->hasFile('image') && !$request->hasFile('file')){
                    $validator->errors()->add('comment','Body or Image or File must be filled in');
                }
            });

            if($validator->fails()){
                return ResponseHelper::error('Validation Error', $validator->errors(), 422);
            }

            $data = [
                'user_id' => Auth::id(),
                'post_id' => $post->id
            ];

            if($request->filled('body')){
                $data['body'] = $request->body;
            }

            if($request->hasFile('image')){
                $path = $request->file('image')->store('comments/images','public');
                $data['image'] = $path;
            }

            if($request->hasFile('file')){
                $path = $request->file('file')->store('comments/files','public');
                $data['file'] = $path;
            }

            $comment = Comment::create($data);
            $comment->load('user:id,name,username,profile_photo');

            return ResponseHelper::success($comment, 'Comment Created Successfully', 201);
        } catch (\Throwable $th) {
            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
        }
    }

    public function update(Request $request, Comment $comment)
    {
        try {
            if(Auth::user()->cannot('update', $comment)){
                return ResponseHelper::error('Unauthorized', 'You are not authorized to update this comment', 403);
            }

            $validator = Validator::make($request->only('body'), [
                'body' => 'nullable|string|max:1000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'file' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,mp4,mov,pdf|max:2048',
            ]);

            $validator->after(function ($validator) use($request){
                if(!$request->filled('body') && !$request->hasFile('image') && !$request->hasFile('file')){
                    $validator->errors()->add('comment','Body or Image or File must be filled in');
                }
            });

            if($validator->fails()){
                return ResponseHelper::error('Validation Error', $validator->errors(), 422);
            }

            $data = [];
            $basePath = 'comments';

            if($request->filled('body')){
                $data['body'] = $request->body;
            }

            if($request->hasFile('image')){
                if($comment->image){
                    Storage::disk('public')->delete($comment->image);
                }

                $path = $request->file('image')->store($basePath.'/images','public');
                $data['image'] = $path;
            }

            if($request->hasFile('file')){
                if($comment->file){
                    Storage::disk('public')->delete($comment->file);
                }

                $path = $request->file('file')->store($basePath.'/files','public');
                $data['file'] = $path;
            }

            $comment->update($data);
            $comment->load('user:id,name,username,profile_photo');
            return ResponseHelper::success($comment->fresh(), 'Comment Updated Successfully', 200);
        } catch (\Throwable $th) {
            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
        }
    }

    public function indexByPost(Post $post)
    {
        try {
        $comments = $post->comments()
        ->with('user:id,name,username,profile_photo')
        ->oldest()
        ->paginate(10);

        return ResponseHelper::success($comments, 'Comment List', 200);
    } catch (\Throwable $th) {
        return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
    }
    }

    public function destroy(Comment $comment)
    {
        try {
            if(Auth::user()->cannot('delete', $comment)){
                return ResponseHelper::error('Unauthorized', 'You are not authorized to delete this comment', 403);
            }

            if($comment->image){
                Storage::disk('public')->delete($comment->image);
            }

            if($comment->file){
                Storage::disk('public')->delete($comment->file);
            }

            $comment->delete();
            return ResponseHelper::success($comment, 'Comment Deleted Successfully', 200);
        } catch (\Throwable $th) {
            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
        }
    }
}

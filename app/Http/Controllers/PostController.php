<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get PAGE & SIZE
        $page = request()->input('page', 0);
        $size = request()->input('size', 10);

        // Get Posts
        $posts = Post::with('user')
            ->orderBy('created_at', 'desc') // urut berdasarkan terbaru
            ->skip($page * $size)
            ->take($size)
            ->get();

        // return response
        return response()->json([
            "page" => $page,
            "size" => $size,
            "posts" => $posts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // validasi
        $validator = Validator::make($request->all(), [
            'caption' => 'required',
            'attachments' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 400);
        }

        // buat post
        $post = new Post();
        $post->caption = $request->caption;
        $post->user_id = auth()->user()->id;
        $post->save();

        // image handler
        $attach = $request->file('attachments');
        $hashAttach = $attach->hashName();
        $attach->storeAs('public/attachments', $hashAttach);

        // store to PostAttachment
        $post->attachments()->create([
            'storage_path' => 'attachments/' . $hashAttach
        ]);

        // return response
        return response()->json([
            "message" => "Create post success"
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Cari post
        $post = Post::with('attachments')->find($id);

        // jika tidak ditemukan
        if (!$post) {
            return response()->json([
                "message" => "Post not found"
            ], 404);
        }

        // jika hapus bukan post miliknya
        if ($post->user_id != auth()->user()->id) {
            return response()->json([
                "message" => "Forbidden"
            ], 403);
        }

        // hapus attachment di storage
        Storage::delete('public/' . $post->attachments->first()->storage_path);

        // hapus attachment di database
        $post->attachments()->delete();
        $post->delete();
    }
}

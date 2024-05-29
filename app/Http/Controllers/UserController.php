<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json($users);
    }

    public function show(string $username)
    {
        /**
         * - is your account
         * - following status
         * - posts count
         * - followers count
         * - following count
         * - posts with storage path
         */

        $user = User::with(['posts.attachments'])
                    ->withCount(['posts', 'followers', 'followings'])
                    ->where('username', $username)
                    ->first();

        if(!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $authId = auth()->user()->id;
        // 1. Is Your Account
        $user->is_your_account = $user->id == $authId;

        // 2. Is Following
        $following = Follow::where('follower_id', $authId)
                            ->where('following_id', $user->id)
                            ->first();
        if(!$following) {
            $user->following_status = "not-following";
        } else if ($following->is_accepted) {
            $user->following_status = "following";
        } else {
            $user->following_status = "requested";
        }

        return response()->json($user);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(string $username)
    {
        // get id user yang di follow
        $userFollowing = User::where('username', $username)->first();

        // ===== VALIDASI ===== //
        // jika user tidak ditemukan
        if(!$userFollowing) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        // get id user yang nge follow
        $followerId = auth()->user()->id;
        // get id user yang di follow
        $followingId = $userFollowing->id;

        // jika user yang di follow adalah diri sendiri
        if($followingId == auth()->user()->id) {
            return response()->json([
                "message" => "You can't follow yourself"
            ], 422);
        }


        // kondisi jika sudah follow
        $follow = Follow::where('follower_id', $followerId)
                        ->where('following_id', $followingId)
                        ->first();
        if($follow) {
            return response()->json([
                "message" => 'Youre already followed',
                "status" => $follow->is_accepted ? "following" : "requested"
            ]);
        }
        // ===== [END] VALIDASI ===== //

        // jika user yang di follow adalah privat
        $isAccepted = $userFollowing->is_private ? false : true;

        // create new follow
        $newFollow = new Follow();
        $newFollow->follower_id = $followerId;
        $newFollow->following_id = $followingId;
        $newFollow->is_accepted = $isAccepted;
        $newFollow->save();

        // return response
        return response()->json([
            "message" => "Follow success",
            "status" => $isAccepted ? "following" : "requested"
        ]);
    }

    public function unfollow(string $username)
    {
        // get id follower dan following
        $followerId = auth()->user()->id;
        $followingUser = User::where('username', $username)->first();

        // jika following user tidak ditemukan
        if(!$followingUser) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        // get data follow
        $unfollowedUser = Follow::where('follower_id', $followerId)
                                ->where('following_id', $followingUser->id)
                                ->where('is_accepted', 1)
                                ->first();

        // jika tidak di follow
        if(!$unfollowedUser) {
            return response()->json([
                "message" => "You are not following the user"
            ], 422);
        }

        // berhasil, maka hapus data di database
        $unfollowedUser->delete();

        return response()->json([], 204);
    }

    public function getFollowing()
    {
        $authId = auth()->user()->id;

        $following = Follow::with('following')
                            ->where('follower_id', $authId)
                            ->where('is_accepted', 1)
                            ->get();

        if(!$following) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        // dapatkan data following
        $followingUsers = $following->pluck('following');

        return response()->json([
            "following" => $followingUsers
        ]);
    }

    public function accept(string $username)
    {
        // get auth id
        $authId = auth()->user()->id;

        // get follower data
        $follower = User::where('username', $username)->first();
        if(!$follower) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        $acceptedFollow = Follow::where('follower_id', $follower->id)
                                ->where('following_id', $authId)
                                ->first();

        if(!$acceptedFollow) {
            return response()->json([
                "message" => "User is not following you"
            ], 422);
        }

        // jika sudah di follow
        if($acceptedFollow->is_accepted) {
            return response()->json([
                "message" => "You are already following the user"
            ], 422);
        }

        // update
        $acceptedFollow->is_accepted = true;
        $acceptedFollow->save();

        return response()->json([
            "message" => "Follow request accepted"
        ]);
    }

    public function getFollowers(string $username)
    {
        // get follower data
        $followingData = User::where('username', $username)->first();
        if(!$followingData) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        // get Id
        $followingId = $followingData->id;

        // get followers
        $followers = Follow::with('follower')
                            ->where('following_id', $followingId) // yang ngefollow dia
                            ->where('is_accepted', 1)
                            ->get();

        // dapatkan data following
        $followersUsers = $followers->pluck('follower');

        return response()->json([
            "followers" => $followersUsers
        ]);
    }
}

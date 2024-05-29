<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // validasi
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'bio' => 'required|max:100',
            'username' => 'required|min:3|unique:users,username|alpha_dash',
            'password' => 'required|min:6',
            'is_private' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid field",
                "errors" => $validator->errors()
            ]);
        }

        // buat is_private
        $isPrivate = $request->has('is_private') ? $request->is_private : 0;

        // buat user
        $user = new User();
        $user->full_name = $request->full_name;
        $user->bio = $request->bio;
        $user->username = $request->username;
        $user->password = bcrypt($request->password);
        $user->is_private = $isPrivate;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        // return response
        return response()->json([
            "message" => "Register success",
            "token" => $token,
            "user" => $user
        ]);
    }

    public function login(Request $request)
    {
        // validasi
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid field",
                "errors" => $validator->errors()
            ]);
        }

        // login gagal
        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json([
                'message' => 'Wrong username and password'
            ], 401);
        }

        // login berhasil
        $user = User::where('username', $request->username)->firstOrFail();

        // buat token
        $token = $user->createToken('auth_token')->plainTextToken;

        // return json
        return response()->json([
            "message" => "Login success",
            "token" => $token,
            "user" => $user
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke token
        $request->user()->currentAccessToken()->delete();

        // return response
        return response()->json(["message" => "Logout success"]);
    }
}

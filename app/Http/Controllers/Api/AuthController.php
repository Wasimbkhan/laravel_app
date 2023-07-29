<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // Register User
    public function registerUser(Request $request)
    {
        try {
            //code...
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => [
                        'required',
                        Password::min(8)
                            ->letters()
                            ->mixedCase()
                            ->numbers()
                            ->symbols()
                            ->uncompromised()
                    ]
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'token' => [
                    'token_type' => 'Bearer',
                    'token' => $user->createToken("API TOKEN")->accessToken
                ]
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'failed',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function loginUser(Request $request)
    {
        try {
            //code...
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 400);
            }

            if (!Auth::attempt($request->all())) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid Credentials',
                    'errors' => 'Email or password wrong'
                ], 400);
            }

            $user = Auth::user();

            return response()->json([
                'status' => 'success',
                'message' => 'User logged in successfully',
                'token' => [
                    'token_type' => 'Bearer',
                    'token' => $user->createToken("API TOKEN")->accessToken
                ]
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'failed',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getUserDetails(Request $request)
    {
        try {
            //code...
            $user = Auth::user();
            return response()->json([
                'status' => 'success',
                'data' => $user
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'failed',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logoutUser(Request $request)
    {

        try {
            //code...
            $user = Auth::user()->token();
            $user->revoke();
            return response()->json([
                'status' => 'success',
                'message' => 'User logged out successfully'
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'failed',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}

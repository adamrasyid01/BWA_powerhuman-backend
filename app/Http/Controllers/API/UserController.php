<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password; 

class UserController extends Controller
{

    //
    public function login(Request $request)
    {

        try {
            // Validate Request
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            //Find user by Email
            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], '500');
            }

            // Generate Token
            $user = User::where('email', $request->email)->first();

            if (!Hash::check($request->password, $user->password, [])) {
                throw new Exception('Invalid Credentials');
            }

            // Return Response
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Login Success');
        } catch (Exception $exception) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $exception
            ], '500');
        }

    }

    public function register(Request $request)
    {
        // dd($request->all());
        try {
            //Validate Request
            $request->validate(['name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string'] 
            ]);
            
            // Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Create Token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            // Return Response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Register Success');
        } catch (Exception $exception) {
            //throw $th;
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $exception->getMessage()
            ], '500');
        }
    }

    public function logout(Request $request){
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Token Revoked');
    }
    public function fetch(Request $request){
        return ResponseFormatter::success($request->user(), 'Data profile user berhasil');
    }
}

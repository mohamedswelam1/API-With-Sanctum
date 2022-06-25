<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class Authcontroller extends Controller
{
    /*
    To issue a token, you may use the createToken method.
    The createToken method returns a Laravel\Sanctum\NewAccessToken instance. 
    API tokens are hashed using SHA-256 hashing before being stored in your database, 
    but you may access the plain-text value of the token using the plainTextToken 
    property of the NewAccessToken instance. You should display this value to the user 
    immediately after the token has been created: 
     */
    public function register(Request $request)
    {
        $fields= $request->validate([
            'name'=>'required|string',
            'email'=>'required|string|unique:users,email',
            'password'=>'required|string|confirmed'
        ]);
        $user= User::create([
            'name'=>$fields['name'],
            'email'=>$fields['email'],
            'password'=> bcrypt($fields['password']),
        ]);
        $token = $user->createtoken('myApptoken')->plainTextToken;
        $response= [
            'user'=> $user , 
            'token'=>$token

        ];
        return response($response,201);

    }
    public function login(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Check password
        if(!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Bad creds'
            ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }
    /*
     You may "revoke" tokens by deleting them from your database using the tokens relationship that is provided by the Laravel\Sanctum\HasApiTokens trait:

    // Revoke all tokens...
    $user->tokens()->delete();
    
    // Revoke the token that was used to authenticate the current request...
    $request->user()->currentAccessToken()->delete();
    
    // Revoke a specific token...
    $user->tokens()->where('id', $tokenId)->delete();
     */
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return [
            'message'=>'Logged out'
        ];
    }
}

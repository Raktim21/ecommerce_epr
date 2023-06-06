<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
   

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if ($token = auth()->attempt($credentials)) {
            $user = auth()->user();
            return response()->json([
                'status' => 'success',
                'user' => $user,
                'admin_access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

    }


    public function profile()
    {
        $user = User::find(auth()->user()->id);
        return response()->json($user, 200);
    }


    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out'],200);
    }


    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
        // return $this->respondWithToken(auth()->fromUser(auth()->user()));
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'admin_access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }


}

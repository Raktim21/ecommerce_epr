<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
   

    public function login(Request $request)
    {
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

    public function updateAvater(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $file = $request->file('avatar');
            $filename = hexdec(uniqid()). '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/uploads/users/avater'),$filename);


            $user = User::find(auth()->user()->id);
            $user->avatar = $filename;
            $user->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Avatar updated successfully'
            ]);
        }catch (\Exception $e){

            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    public function updateInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.auth()->user()->id,
            'phone' =>  [
                            'required',
                            'max:11',
                            'min:11',
                            'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                            'string',
                            'unique:users,phone,'.auth()->user()->id,
                        ],

            'address' => 'nullable|string',
            'details' => 'nullable|string',        
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::find(auth()->user()->id);
            $user->name    = $request->name;
            $user->email   = $request->email;
            $user->phone   = $request->phone;
            $user->address = $request->address;
            $user->details = $request->details;
            $user->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Information updated successfully'
            ]);

        }catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
 

    }


    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' =>  [
                                    'required', function ($attribute, $value, $fail) {
                                        if (!Hash::check($value, auth()->user()->password)) {
                                            $fail("Old password is incorrect");
                                        }
                                    },
                                ],
            'new_password'     => 'required',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::find(auth()->user()->id);
            $user->password = bcrypt($request->new_password);
            $user->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully'
            ]);


        }catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

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

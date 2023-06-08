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
                'success' => false,
                'error' => $validator->errors()->first()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if ($token = auth()->attempt($credentials))
        {
            $user = auth()->user();
            return response()->json([
                'success' => true,
                'user' => $user,
                'admin_access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        }
        else
        {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized'
            ], 401);
        }

    }


    public function profile()
    {
        $user = User::find(auth()->user()->id);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function updateAvater(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
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
                'success' => true,
            ]);
        }
        catch (\Exception $e)
        {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function updateInfo(Request $request)
    {
        $user = User::findOrFail(auth()->user()->id);

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.auth()->user()->id,
            'phone' =>  [
                            'required',
                            'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                            'unique:users,phone,'.$user->id,
                        ],

            'address' => 'nullable|string',
            'details' => 'nullable|string',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'details' => $request->details,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }


    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' =>  [
                                    'required', function ($attribute, $value, $fail) {
                                        if (!Hash::check($value, auth()->user()->password)) {
                                            $fail("Old password is incorrect.");
                                        }
                                    },
                                ],
            'new_password'     => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ], 422);
        }

        User::findOrFail(auth()->user()->id)->update([
            'password' => bcrypt($request->new_password)
        ]);

        return response()->json([
            'success' => true,
        ]);
    }


    public function logout()
    {
        auth()->logout();

        return response()->json([
            'success' => true
        ]);
    }


    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
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

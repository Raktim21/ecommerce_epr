<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $user = User::paginate(10);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|string|email|max:255|unique:users,email',
            'phone'   =>   [
                                'required',
                                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                                'unique:users,phone',
                            ],
            'address'          => 'nullable|string',
            'details'          => 'nullable|string',
            'avatar'           => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'password'         => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $file = $request->file('avatar');
        $filename = hexdec(uniqid()). '.' . $file->getClientOriginalExtension();
        $file->move(public_path('/uploads/users/avatar'),$filename);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'details' => $request->details,
            'password' => Hash::make($request->password),
            'avatar' => $filename
        ]);

        return response()->json([
            'success' => true,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|string|email|max:255|unique:users,email,'.$id,
            'phone'   =>   [
                                'required',
                                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                                'string',
                                'unique:users,phone,'.$id,
                            ],

            'address'          => 'nullable|string',
            'details'          => 'nullable|string',
            'avatar'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $user = User::find($id)->update($request->except('avatar'));

        if ($request->hasFile('avatar')) {

            $file = $request->file('avatar');
            $filename = hexdec(uniqid()). '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/uploads/users/avatar'),$filename);

            $user->avatar = $filename;
            $user->save();
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }
}
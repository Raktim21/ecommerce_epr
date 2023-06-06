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
        $user = User::paginate(15);

        return response()->json([

            'success' => true,
            'data' => $user

        ],200);
    }




    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|string|email|max:255|unique:users,email',
            'phone'   =>   [
                                'required',
                                'max:11',
                                'min:11',
                                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                                'string',
                                'unique:users,phone',
                            ],

            'address'          => 'nullable|string',
            'details'          => 'nullable|string',  
            'avatar'           => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'password'         => 'required|string|min:6',
            'confirm_password' => 'required|string|min:6|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }


        DB::beginTransaction();

        try {

            $user = new User();
            $user->name     = $request->name;
            $user->email    = $request->email;
            $user->phone    = $request->phone;
            $user->address  = $request->address;
            $user->details  = $request->details;
            $user->password = Hash::make($request->password);
            $user->save();
    
            $file = $request->file('avatar');
            $filename = hexdec(uniqid()). '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/uploads/users/avater'),$filename);
    
    
            $user->avatar = $filename;
            $user->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User created successfully'
            ]);


        }catch (\Exception $e){

            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);

        }
    }



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|string|email|max:255|unique:users,email,'.$id,
            'phone'   =>   [
                                'required',
                                'max:11',
                                'min:11',
                                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                                'string',
                                'unique:users,phone,'.$id,
                            ],

            'address'          => 'sometimes|nullable|string',
            'details'          => 'sometimes|nullable|string',  
            'avatar'           => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }


        DB::beginTransaction();

        try {

            $user = User::find($id)->update($request->except('avatar'));
    
            if ($request->hasFile('avatar')) {

                $file = $request->file('avatar');
                $filename = hexdec(uniqid()). '.' . $file->getClientOriginalExtension();
                $file->move(public_path('/uploads/users/avater'),$filename);
        
                $user->avatar = $filename;
                $user->save();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User updated successfully'
            ]);


        }catch (\Exception $e){

            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);

        }
    }



    public function show($id)
    {
        $validator = Validator::make($id, [
            'id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($id);

        return response()->json([
            'success' => true,
            'data' => $user
        ],200);
    }
}

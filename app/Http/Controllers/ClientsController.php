<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientsController extends Controller
{
    public function index()
    {
        $data = Clients::paginate(10);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company' => 'required|string|max:255',
            'name'    => 'required|string|max:255',
            'email'   => 'required|string|email|max:255|unique:clients,email',
            'area'          => 'required|string',
            'phone'   =>   [
                'required',
                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                'unique:users,phone',
            ],
            'address'          => 'nullable|string',
            'avatar'           => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'password'         => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
        ]);
    }
}

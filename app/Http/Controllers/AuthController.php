<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
   

    /**
     * @OA\Post(
     *     path="/api/admin/login",
     *     operationId="adminLogin",
     *     tags={"Authentications"},
     *     summary="Authenticates admin",
     *     description="Returns admin user information and a bearer token",
     *
     *     @OA\Parameter(
     *         name="email",
     *         required=true,
     *         description="Email of the admin",
     *         in="query",
     *       
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         required=true,
     *         description="Password of the admin",
     *         in="query",
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="admin@admin.com"),
     *             @OA\Property(property="password", type="string", example="12345678"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User login successfully"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property( 
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="email", type="string", example="The email field is required."),
     *                 @OA\Property(property="password", type="string", example="The password field is required."),
     *             ),  
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized"),
     *         ),
     *     ),
     * )
    */
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

        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            return response()->json([
                'status' => 'success',
                'user' => $user,
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

    }


    /**
     * @OA\Get(
     *      path="/api/admin/me",
     *      operationId="getAdminProfileInfo",
     *      tags={"Admin profile"},
     *      summary="Get admin profile information",
     *      description="Returns admin profile information",
     *      security={{"bearerAuth":{}}},
     *
     * 
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="1"),
     *              @OA\Property(property="name", type="string", example="Admin"),
     *              @OA\Property(property="email", type="string", example="admin@admin.com"),
     *              @OA\Property(property="phone", type="string", example="01700000000"), 
     *              @OA\Property(property="last_login", type="string", example="2021-05-05 12:00:00"),
     *              @OA\Property(property="email_verified_at", type="string", example="2021-05-05 12:00:00"),
     *              @OA\Property(property="created_at", type="string", example="2021-05-05 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2021-05-05 12:00:00"),
     *              @OA\Property(
     *                  property="roles", 
     *                  type="array", 
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="integer", example="1"),
     *                      @OA\Property(property="name", type="string", example="Admin"),
     *                      @OA\Property(property="guard_name", type="string", example="admin-api"),
     *                      @OA\Property(property="created_at", type="string", example="2021-05-05 12:00:00"),
     *                      @OA\Property(property="updated_at", type="string", example="2021-05-05 12:00:00"),
     *                  ),
     *              ), 
     *          ),     
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Unauthenticated"),
     *              @OA\Property(property="message", type="string", example="Unauthenticated"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Forbidden"),
     *              @OA\Property(property="message", type="string", example="Forbidden"),
     *          ),
     *      )
     *  ),
    */
    public function me()
    {
        $user = User::find(auth()->user()->id);
        return response()->json($user, 200);
    }


    /**
     * @OA\Post(
     *      path="/api/admin/logout",
     *      operationId="AdminLogout",
     *      tags={"Authentications"},
     *      summary="Admin Logout",
     *      description="For admin logout",
     *      security={{"bearerAuth":{}}},
     * 
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Successfully logged out"),
     *              @OA\Property(property="status", type="integer", example="200"),
     *              @OA\Property(property="success", type="boolean", example="true"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Bad Request"),
     *              @OA\Property(property="message", type="string", example="Bad Request"),
     *              @OA\Property(property="status", type="integer", example="400"),
     *              @OA\Property(property="success", type="boolean", example="false"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Unauthenticated"),
     *              @OA\Property(property="message", type="string", example="Unauthenticated"),
     *              @OA\Property(property="status", type="integer", example="401"),
     *              @OA\Property(property="success", type="boolean", example="false"),
     *              @OA\Property(property="data", type="object", example="null"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Forbidden"),
     *              @OA\Property(property="message", type="string", example="Forbidden"),
     *              @OA\Property(property="status", type="integer", example="403"),
     *              @OA\Property(property="success", type="boolean", example="false"),
     *              @OA\Property(property="data", type="object", example="null"),
     *         ),
     *      )
     * )
    */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out'],200);
    }


    /**
     * @OA\Get(
     *      path="/api/admin/refresh",
     *      operationId="updateAdminToken",
     *      tags={"Admin profile"},
     *      summary="Update admin token",
     *      description="Returns admin updated token",
     *      security={{"bearerAuth":{}}},
     *
     * 
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="admin_access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...."),
     *              @OA\Property(property="token_type", type="string", example="bearer"),
     *              @OA\Property(property="expires_in", type="integer", example="3600"),
     *          ),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Unauthenticated"),
     *              @OA\Property(property="message", type="string", example="Unauthenticated"),
     *              @OA\Property(property="status", type="integer", example="401"),
     *              @OA\Property(property="success", type="boolean", example="false"),
     *              @OA\Property(property="data", type="object", example="null"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Forbidden"),
     *              @OA\Property(property="message", type="string", example="Forbidden"),
     *              @OA\Property(property="status", type="integer", example="403"),
     *              @OA\Property(property="success", type="boolean", example="false"),
     *              @OA\Property(property="data", type="object", example="null"),
     *          ),
     *      )
     * )
    */
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

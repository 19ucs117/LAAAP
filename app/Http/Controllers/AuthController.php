<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'me']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['department_number', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function editUser(Request $request)
    {
        $status = "";
        $message = "";
        $request->validate([
            'id' => ['required'],
            'name' => ['required'],
            'email' => ['required'],
            'mobileNumber' => ['required'],
        ]);
        try {
            if ($user = User::find($request->id)) {
                $user->name              = $request->name;
                $user->email             = $request->email;
                $user->phone_number      = $request->mobileNumber;
                $user->save();
            }
            $status = 'success';
            $message = "User Updated Successfully";
        } catch (Exception $e) {
            Log::warning('Error Updating User', $e->getMessage());
            $status = 'error';
            $message = "Unable to Update User";
        }

        return response()->json(['status' => $status, 'message' => $message]);
    }

    public function editPassword(Request $request)
    {
        $status = "";
        $message = "";
        $request->validate([
            'id' => ['required'],
            'password' => ['required']
        ]);
        try {
            if ($user = User::find($request->id)) {
                $user->password = bcrypt($request->password);
                $user->save();
            }
            $status = 'success';
            $message = "Password Updated Successfully";
        } catch (Exception $e) {
            Log::warning('Error Updating Password', $e->getMessage());
            $status = 'error';
            $message = "Unable to Update Password";
        }

        return response()->json(['status' => $status, 'message' => $message]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = User::join('roles', 'roles.id', '=', 'users.role_id')
            ->join('departments', 'departments.id', '=', 'users.department_id')
            ->select('users.id', 'users.phone_number', 'users.is_active as active', 'users.name', 'users.email', 'users.department_number', 'roles.role_name', 'departments.school_id', 'departments.id as department_id', 'departments.department_name')
            ->where('users.id', auth()->user()->id)
            ->first();

        return response()->json($user);
    }

    public function role()
    {
        $user = User::join('roles', 'roles.id', '=', 'users.role_id')->select('users.name', 'roles.role_name')
            ->where('users.id', auth()->user()->id)
            ->first();

        return response()->json([
            'role' => $user->role_name,
        ]);
    }
    public function isLogin()
    {
        if (auth()->user() != null) {
            return response()->json(['status' => true]);
        } else {
            auth()->logout();
            return response()->json(['status' => false]);
        }
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['status' => true, 'message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }
}

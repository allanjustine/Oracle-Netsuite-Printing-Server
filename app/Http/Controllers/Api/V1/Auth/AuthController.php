<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'branchCode'    =>  ['required', 'string'],
            'password'      =>  ['required', 'string', 'min:6'],
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message'       =>  'Something went wrong. Please fix.',
                "errors"        =>  $validation->errors()
            ], 400);
        }

        $user = User::where('username', $request->branchCode)
            ->orWhere('email', $request->branchCode)
            ->first();

        if (!$user || $user->email_verified_at === null) {
            return response()->json([
                'message'       =>  'Username or email not found. Or email is not verified yet.',
            ], 400);
        }

        if (Auth::guard('web')->attempt([
            'email'         =>  filter_var($request->branchCode, FILTER_VALIDATE_EMAIL) ? $request->branchCode : $user->email,
            'password'      =>  $request->password
        ])) {

            $request->session()->regenerate();

            return response()->json([
                'message'       =>  'Login successfully.',
            ], 200);
        }

        return response()->json([
            'message'       =>  'Invalid credentials.',
        ], 400);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message'       =>  'You logout successfully.',
        ], 200);
    }
}

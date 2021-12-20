<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function login(Request $request){
        $valid = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $user = User::with('company')->where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password) || $user->status == 'inactive' || ($user->company != null && $user->company->status == 'inactive')) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }
        $user->tokens()->delete();
        return response()->json(['access_token' => $user->createToken('Personal Access Token')->plainTextToken], 200);
    }

    public function logout(){
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Successfully logged out.'], 200);
    }

    public function forgotPassword(Request $request){
        $valid = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        Password::sendResetLink(
            $request->only('email')
        );

        return response()->json(['message' => 'An email has been sent to your email.'], 200);
    }

    public function resetPassword(Request $request){
        $valid = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors()->all(), 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );
        
        if($status == Password::PASSWORD_RESET){
            return response()->json(['message' => 'Password has been resetted.'], 200);
        } else {
            return response()->json(['message' => 'Failed to reset password.'], 200);
        }
    }
}

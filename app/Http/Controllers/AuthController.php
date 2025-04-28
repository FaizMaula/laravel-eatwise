<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Models\OtpVerification;
use App\Mail\SendOtpMail;



class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users',
            'fullname' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'username' => $request->username,
            'fullname' => $request->fullname,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Signup successful'], 200);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Generate token
        $token = $user->createToken("API TOKEN")->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token
        ], 200);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'fullname' => 'required|string',
            'username' => 'required|string',
        ]);

        $otp = rand(1000, 9999);

        OtpVerification::updateOrCreate(
            ['email' => $request->email],
            ['otp' => $otp, 'updated_at' => now()]
        );

        Mail::to($request->email)->send(new SendOtpMail($otp, $request->fullname, $request->username));

        return response()->json(['message' => 'OTP sent']);
    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $otpData = OtpVerification::where('email', $request->email)->first();

        if ($otpData && $otpData->otp == $request->otp) {
            $otpData->delete();
            return response()->json(['message' => 'OTP verified']);
        }

        return response()->json(['message' => 'Invalid OTP'], 422);
    }

    public function checkEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email'
    ]);

    $user = User::where('email', $request->email)->first();

    if ($user) {
        // Generate OTP
        $otp = rand(1000, 9999);

        OtpVerification::updateOrCreate(
            ['email' => $request->email],
            ['otp' => $otp, 'updated_at' => now()]
        );

        // Kirim OTP menggunakan Blade view
        Mail::send('emails.otp_reset', ['otp' => $otp], function ($message) use ($request) {
            $message->to($request->email)->subject("Reset Password OTP Verification");
        });

        return response()->json([
            'status' => true,
            'message' => 'Email is registered. OTP sent to your email.'
        ]);
    } else {
        return response()->json([
            'status' => false,
            'message' => 'Email is not registered.'
        ], 404);
    }
}


    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'new_password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password reset successful'], 200);
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'email' => 'required|email',
        ]);

        $usernameExists = User::where('username', $request->username)->exists();
        $emailExists = User::where('email', $request->email)->exists();

        if ($usernameExists) {
            return response()->json([
                'message' => 'Username already taken',
            ], 409); // 409 Conflict
        }

        if ($emailExists) {
            return response()->json([
                'message' => 'Email already registered',
            ], 409); // 409 Conflict
        }

        return response()->json([
            'message' => 'Available',
        ], 200);
    }
}

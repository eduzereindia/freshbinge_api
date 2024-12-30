<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Register Step 1: Initial registration and send OTPs
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mobile' => 'required|string|size:10|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Store user data in session
        $request->session()->put('registration_data', $request->all());

        // Send OTPs
        $this->otpService->generateAndSendOtp($request->email, 'email');
        $this->otpService->generateAndSendOtp($request->mobile, 'mobile');

        // Check if mobile is WhatsApp number
        if ($this->otpService->isWhatsappNumber($request->mobile)) {
            $this->otpService->generateAndSendOtp($request->mobile, 'whatsapp');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'OTPs sent successfully',
            'data' => [
                'email' => $request->email,
                'mobile' => $request->mobile,
                'whatsapp_enabled' => $this->otpService->isWhatsappNumber($request->mobile)
            ]
        ]);
    }

    /**
     * Register Step 2: Verify OTPs and complete registration
     */
    public function verifyRegistrationOtps(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_otp' => 'required|string|size:6',
            'mobile_otp' => 'required|string|size:6',
            'whatsapp_otp' => 'required_if:whatsapp_enabled,true|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get registration data from session
        $registrationData = $request->session()->get('registration_data');
        
        if (!$registrationData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration session expired'
            ], 400);
        }

        // Verify OTPs
        $emailVerified = $this->otpService->verifyOtp($registrationData['email'], 'email', $request->email_otp);
        $mobileVerified = $this->otpService->verifyOtp($registrationData['mobile'], 'mobile', $request->mobile_otp);
        
        $whatsappVerified = true;
        if ($this->otpService->isWhatsappNumber($registrationData['mobile'])) {
            $whatsappVerified = $this->otpService->verifyOtp($registrationData['mobile'], 'whatsapp', $request->whatsapp_otp);
        }

        if (!$emailVerified || !$mobileVerified || !$whatsappVerified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP'
            ], 400);
        }

        // Create user
        $user = User::create([
            'name' => $registrationData['name'],
            'email' => $registrationData['email'],
            'mobile' => $registrationData['mobile'],
            'password' => Hash::make($registrationData['password']),
            'email_verified_at' => now(),
            'mobile_verified_at' => now()
        ]);

        // Clear session
        $request->session()->forget('registration_data');

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    /**
     * Login Step 1: Send OTPs
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Store user ID in session
        $request->session()->put('login_user_id', $user->id);

        // Send OTPs
        $this->otpService->generateAndSendOtp($user->email, 'email');
        $this->otpService->generateAndSendOtp($user->mobile, 'mobile');

        // Check if mobile is WhatsApp number
        if ($this->otpService->isWhatsappNumber($user->mobile)) {
            $this->otpService->generateAndSendOtp($user->mobile, 'whatsapp');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'OTPs sent successfully',
            'data' => [
                'email' => $user->email,
                'mobile' => $user->mobile,
                'whatsapp_enabled' => $this->otpService->isWhatsappNumber($user->mobile)
            ]
        ]);
    }

    /**
     * Login Step 2: Verify OTPs and complete login
     */
    public function verifyLoginOtps(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_otp' => 'required|string|size:6',
            'mobile_otp' => 'required|string|size:6',
            'whatsapp_otp' => 'required_if:whatsapp_enabled,true|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get user ID from session
        $userId = $request->session()->get('login_user_id');
        
        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login session expired'
            ], 400);
        }

        $user = User::find($userId);

        // Verify OTPs
        $emailVerified = $this->otpService->verifyOtp($user->email, 'email', $request->email_otp);
        $mobileVerified = $this->otpService->verifyOtp($user->mobile, 'mobile', $request->mobile_otp);
        
        $whatsappVerified = true;
        if ($this->otpService->isWhatsappNumber($user->mobile)) {
            $whatsappVerified = $this->otpService->verifyOtp($user->mobile, 'whatsapp', $request->whatsapp_otp);
        }

        if (!$emailVerified || !$mobileVerified || !$whatsappVerified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP'
            ], 400);
        }

        // Clear session
        $request->session()->forget('login_user_id');

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // email or mobile
            'type' => 'required|in:email,mobile,whatsapp'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $this->otpService->generateAndSendOtp($request->identifier, $request->type);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP resent successfully'
        ]);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class AuthController
 * 
 * Handles all authentication related operations including registration,
 * login (both password and OTP based), and OTP verification.
 */
class AuthController extends Controller
{
    /**
     * @var OtpService
     */
    protected $otpService;

    /**
     * AuthController constructor.
     * 
     * @param OtpService $otpService Service for handling OTP operations
     */
    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Register a new user.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws ValidationException
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|size:10|unique:users',
            'password' => 'required|string|min:8',
            'email' => 'nullable|email|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'password' => Hash::make($request->password),
            'email' => $request->email
        ]);

        // Send OTPs for verification
        $this->otpService->generateAndSendOtp($user->mobile, 'mobile');
        
        if ($this->otpService->isWhatsappNumber($user->mobile)) {
            $this->otpService->generateAndSendOtp($user->mobile, 'whatsapp');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful. Please verify your mobile number.',
            'data' => [
                'user' => $user,
                'whatsapp_enabled' => $this->otpService->isWhatsappNumber($user->mobile)
            ]
        ], 201);
    }

    /**
     * Verify registration OTPs.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws ValidationException
     */
    public function verifyRegistrationOtps(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|size:10|exists:users',
            'mobile_otp' => 'required|string|size:6',
            'whatsapp_otp' => 'required_if:whatsapp_enabled,true|string|size:6',
            'whatsapp_enabled' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('mobile', $request->mobile)->first();

        // Verify OTPs
        $mobileVerified = $this->otpService->verifyOtp($user->mobile, 'mobile', $request->mobile_otp);
        
        $whatsappVerified = true;
        if ($request->whatsapp_enabled) {
            $whatsappVerified = $this->otpService->verifyOtp($user->mobile, 'whatsapp', $request->whatsapp_otp);
        }

        if (!$mobileVerified || !$whatsappVerified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP'
            ], 400);
        }

        // Mark user as verified
        $user->markPhoneAsVerified();
        
        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Mobile number verified successfully',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    /**
     * Login with mobile using either password or OTP.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|size:10',
            'login_type' => 'required|in:password,otp',
            'password' => 'required_if:login_type,password|string',
            'mobile_otp' => 'required_if:login_type,otp|string|size:6',
            'whatsapp_otp' => 'required_if:whatsapp_enabled,true|string|size:6',
            'whatsapp_enabled' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        if ($request->login_type === 'password') {
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid password'
                ], 401);
            }
        } else {
            // Verify OTPs
            $mobileVerified = $this->otpService->verifyOtp($user->mobile, 'mobile', $request->mobile_otp);
            
            $whatsappVerified = true;
            if ($request->whatsapp_enabled) {
                $whatsappVerified = $this->otpService->verifyOtp($user->mobile, 'whatsapp', $request->whatsapp_otp);
            }

            if (!$mobileVerified || !$whatsappVerified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid OTP'
                ], 400);
            }
        }

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
     * Request OTP for login.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws ValidationException
     */
    public function requestLoginOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|size:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Send OTPs
        $this->otpService->generateAndSendOtp($user->mobile, 'mobile');

        // Check if mobile is WhatsApp number
        $whatsappEnabled = false;
        if ($this->otpService->isWhatsappNumber($user->mobile)) {
            $this->otpService->generateAndSendOtp($user->mobile, 'whatsapp');
            $whatsappEnabled = true;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'OTPs sent successfully',
            'data' => [
                'mobile' => $user->mobile,
                'whatsapp_enabled' => $whatsappEnabled
            ]
        ]);
    }

    /**
     * Resend OTP.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws ValidationException
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|size:10',
            'type' => 'required|in:mobile,whatsapp'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        if ($request->type === 'whatsapp' && !$this->otpService->isWhatsappNumber($user->mobile)) {
            return response()->json([
                'status' => 'error',
                'message' => 'WhatsApp not enabled for this number'
            ], 400);
        }

        // Resend OTP
        $this->otpService->generateAndSendOtp($user->mobile, $request->type);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP resent successfully'
        ]);
    }

    /**
     * Logout user and revoke token.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }
}

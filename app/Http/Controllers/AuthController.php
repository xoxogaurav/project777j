<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            $result = $this->authService->register($request->all());
            return $this->successResponse($result, 'User registered successfully', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 'VALIDATION_ERROR');
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed', 'REGISTRATION_ERROR');
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $result = $this->authService->login($request->only('email', 'password'));
            return $this->successResponse($result, 'Login successful');
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 'INVALID_CREDENTIALS');
        }
    }

    public function logout()
    {
        auth()->logout();
        return $this->successResponse(null, 'Successfully logged out');
    }

    public function refresh()
    {
        try {
            $token = auth()->refresh();
            return $this->successResponse([
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Token refresh failed', 'TOKEN_REFRESH_ERROR');
        }
    }
}
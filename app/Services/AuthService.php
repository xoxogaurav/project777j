<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'balance' => 0,
            'pending_earnings' => 0,
            'total_withdrawn' => 0,
            'tasks_completed' => 0,
            'success_rate' => 0,
            'average_rating' => 0,
        ]);

        $token = auth()->login($user);

        return [
            'token' => $token,
            'user' => $this->formatUserData($user)
        ];
    }

    public function login(array $credentials)
    {
        if (!$token = auth()->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return [
            'token' => $token,
            'user' => $this->formatUserData(auth()->user())
        ];
    }

    private function formatUserData(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
        ];
    }
}
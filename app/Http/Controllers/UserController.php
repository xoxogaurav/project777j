<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    use ApiResponse;

    public function profile()
    {
        try {
            $user = auth()->user();
            return $this->successResponse([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'balance' => $user->balance,
                'pendingEarnings' => $user->pending_earnings,
                'totalWithdrawn' => $user->total_withdrawn,
                'tasksCompleted' => $user->tasks_completed,
                'successRate' => $user->success_rate,
                'averageRating' => $user->average_rating,
                'country' => $user->country,
                'age' => $user->age,
                'phoneNumber' => $user->phone_number,
                'bio' => $user->bio,
                'timezone' => $user->timezone,
                'language' => $user->language,
                'emailNotifications' => $user->email_notifications
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch profile', 'FETCH_ERROR');
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'name' => 'string|max:255',
                'country' => 'nullable|string|max:255',
                'age' => 'nullable|integer|min:18|max:120',
                'phoneNumber' => 'nullable|string|max:20',
                'bio' => 'nullable|string',
                'timezone' => 'nullable|string',
                'language' => 'nullable|string',
                'emailNotifications' => 'boolean',
                'currentPassword' => 'required_with:newPassword',
                'newPassword' => 'nullable|min:6|confirmed',
            ]);

            if ($request->has('currentPassword')) {
                if (!Hash::check($request->currentPassword, $user->password)) {
                    throw ValidationException::withMessages([
                        'currentPassword' => ['Current password is incorrect'],
                    ]);
                }
            }

            $updateData = [
                'name' => $request->name,
                'country' => $request->country,
                'age' => $request->age,
                'phone_number' => $request->phoneNumber,
                'bio' => $request->bio,
                'timezone' => $request->timezone,
                'language' => $request->language,
                'email_notifications' => $request->emailNotifications
            ];

            if ($request->filled('newPassword')) {
                $updateData['password'] = Hash::make($request->newPassword);
            }

            $user->update($updateData);

            return $this->successResponse([
                'user' => $this->profile()->original['data']
            ], 'Profile updated successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 'VALIDATION_ERROR');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update profile', 'UPDATE_ERROR');
        }
    }

    public function leaderboard()
    {
        try {
            $users = User::where('is_admin', false)
                ->orderByDesc('balance')
                ->select('name', 'balance', 'tasks_completed', 'profile_picture')
                ->limit(10)
                ->get();

            return $this->successResponse($users);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch leaderboard', 'FETCH_ERROR');
        }
    }
}
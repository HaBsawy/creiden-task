<?php

namespace App\Http\Controllers\API\User;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\LoginRequest;
use App\Http\Requests\API\User\RegisterRequest;
use App\Http\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = UserService::create($request);
            $token = $user->createToken('user');
            $user = [
                'name'  => $user->name,
                'email' => $user->email,
                'token' => $token->plainTextToken,
            ];

            return ResponseHelper::make($user, 'The user registered successfully',
                true, 201);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = UserService::getByEmail($request->get('email'));
            if (!$user) {
                return ResponseHelper::notAuthenticated();
            }

            if (Hash::check($request->get('password'), $user->password)) {
                $token = $user->createToken('user');
                $user = [
                    'name'  => $user->name,
                    'email' => $user->email,
                    'token' => $token->plainTextToken,
                ];
                return ResponseHelper::make($user, 'Login Successfully',
                    true, 202);
            } else {
                return ResponseHelper::notAuthenticated();
            }
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }

    public function logout(): JsonResponse
    {
        try {
            auth('user')->user()->currentAccessToken()->delete();

            return ResponseHelper::make(null, 'Logout Successfully',
                true, 202);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }
}

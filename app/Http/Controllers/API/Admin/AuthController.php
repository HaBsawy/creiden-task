<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Admin\LoginRequest;
use App\Http\Requests\API\Admin\RegisterRequest;
use App\Http\Services\AdminService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $admin = AdminService::create($request);
            $token = $admin->createToken('admin');
            $admin = [
                'name'  => $admin->name,
                'email' => $admin->email,
                'token' => $token->plainTextToken,
            ];

            return ResponseHelper::make($admin, 'The admin registered successfully',
                true, 201);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $admin = AdminService::getByEmail($request->get('email'));
            if (!$admin) {
                return ResponseHelper::notAuthenticated();
            }

            if (Hash::check($request->get('password'), $admin->password)) {
                $token = $admin->createToken('admin');
                $admin = [
                    'name'  => $admin->name,
                    'email' => $admin->email,
                    'token' => $token->plainTextToken,
                ];
                return ResponseHelper::make($admin, 'Login Successfully',
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
            auth('admin')->user()->currentAccessToken()->delete();
            return ResponseHelper::make(null, 'Logout Successfully',
                true, 202);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }
}

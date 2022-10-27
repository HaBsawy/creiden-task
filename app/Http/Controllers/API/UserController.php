<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\UserRequest;
use App\Http\Services\UserService;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            return ResponseHelper::make(UserService::getWithStorage());
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserRequest $request
     * @return JsonResponse
     */
    public function store(UserRequest $request): JsonResponse
    {
        try {
            $user = UserService::create($request);
            return ResponseHelper::make($user, 'The user created successfully',
                true, 201);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        return ResponseHelper::make($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        try {
            UserService::update($user, $request);
            return ResponseHelper::make($user, 'The user updated successfully',
                true, 202);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $user->delete();
            return ResponseHelper::make(null, 'The user deleted successfully',
                true, 202);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }
}

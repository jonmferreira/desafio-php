<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Limite de per_page protege contra Unrestricted Resource Consumption (API4).
        $perPage = min((int) $request->query('per_page', 15), 100);

        return response()->json(
            User::query()->paginate($perPage)
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::query()->create($request->validated());

        return response()->json($user, 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json($user);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $user->update($request->validated());

        return response()->json($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json(null, 204);
    }

    public function movements(User $user, Request $request): JsonResponse
    {
        $this->authorize('view', $user);

        $perPage = min((int) $request->query('per_page', 10), 50);

        return response()->json(
            $user->stockMovements()->with('product')->latest()->paginate($perPage)
        );
    }
}

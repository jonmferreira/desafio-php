<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Usuario so pode ver/editar/excluir seus proprios dados, exceto admin (IDOR - API1).
     */
    public function view(User $authUser, User $user): bool
    {
        return $authUser->id === $user->id || $authUser->role === 'admin';
    }

    public function update(User $authUser, User $user): bool
    {
        return $authUser->id === $user->id || $authUser->role === 'admin';
    }

    public function delete(User $authUser, User $user): bool
    {
        return $authUser->id === $user->id || $authUser->role === 'admin';
    }
}

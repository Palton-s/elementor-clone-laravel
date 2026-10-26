<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(?User $user): bool
    {
        return $user !== null;
    }

    public function view(?User $user, Page $page): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return (int) $page->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function update(User $user, Page $page): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isViewer()) {
            return false;
        }

        return (int) $page->user_id === (int) $user->id;
    }

    public function delete(User $user, Page $page): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int) $page->user_id === (int) $user->id;
    }

    public function restore(User $user, Page $page): bool
    {
        return $this->delete($user, $page);
    }

    public function forceDelete(User $user, Page $page): bool
    {
        return $user->isAdmin();
    }
}

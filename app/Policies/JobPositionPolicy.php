<?php

namespace App\Policies;

use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobPositionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, JobPosition $jobPosition): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, JobPosition $jobPosition): bool
    {
    }

    public function delete(User $user, JobPosition $jobPosition): bool
    {
    }

    public function restore(User $user, JobPosition $jobPosition): bool
    {
    }

    public function forceDelete(User $user, JobPosition $jobPosition): bool
    {
    }
}

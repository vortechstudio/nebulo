<?php

namespace App\Policies;

use App\Models\Bucket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Query\Builder;

class BucketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Bucket $bucket): bool
    {
        return $user->id === $bucket->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bucket $bucket): bool
    {
        return $user->id === $bucket->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bucket $bucket): bool
    {
        return $user->id === $bucket->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Bucket $bucket): bool
    {
        return $user->id === $bucket->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Bucket $bucket): bool
    {
        return $user->id === $bucket->user_id;
    }

    /**
     * Scope a query to only include buckets owned by the user.
     */
    public function scopeViewAny(User $user, Builder $query): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Apply view scope for individual records.
     */
    public function scopeView(User $user, Builder $query): Builder
    {
        return $query->where('user_id', $user->id);
    }
}

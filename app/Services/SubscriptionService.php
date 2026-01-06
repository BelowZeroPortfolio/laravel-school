<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SubscriptionService
{
    /**
     * Get all teachers with their premium status and expiration dates.
     * (Requirement 20.1)
     *
     * @return Collection Collection of teachers with subscription info
     */
    public function getTeachersWithSubscriptions(): Collection
    {
        return User::teachers()
            ->select(['id', 'username', 'full_name', 'email', 'is_active', 'is_premium', 'premium_expires_at'])
            ->orderBy('full_name')
            ->get();
    }

    /**
     * Grant premium access to a user.
     * Sets is_premium to true and premium_expires_at to the specified date.
     * (Requirement 20.2)
     *
     * @param int $userId The user's ID
     * @param Carbon $expiresAt The expiration date for premium access
     * @return bool True if successful
     */
    public function grantPremium(int $userId, Carbon $expiresAt): bool
    {
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        $user->update([
            'is_premium' => true,
            'premium_expires_at' => $expiresAt,
        ]);

        return true;
    }

    /**
     * Revoke premium access from a user.
     * Sets is_premium to false.
     * (Requirement 20.3)
     *
     * @param int $userId The user's ID
     * @return bool True if successful
     */
    public function revokePremium(int $userId): bool
    {
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        $user->update([
            'is_premium' => false,
        ]);

        return true;
    }

    /**
     * Check if a user's premium subscription is currently active.
     * Premium is active if is_premium is true AND premium_expires_at is in the future (or null).
     * (Requirement 20.4)
     *
     * @param User $user The user to check
     * @return bool True if premium is active
     */
    public function isPremiumActive(User $user): bool
    {
        // If not marked as premium, return false
        if (!$user->is_premium) {
            return false;
        }

        // If premium_expires_at is null, premium is active indefinitely
        if ($user->premium_expires_at === null) {
            return true;
        }

        // Check if expiration date is in the future
        return Carbon::parse($user->premium_expires_at)->isFuture();
    }
}

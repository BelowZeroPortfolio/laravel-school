<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Display a listing of all teachers with their subscription status.
     * (Requirement 20.1)
     */
    public function index(): View
    {
        $teachers = $this->subscriptionService->getTeachersWithSubscriptions();

        // Add computed premium active status for each teacher
        $teachers->each(function ($teacher) {
            $teacher->premium_active = $this->subscriptionService->isPremiumActive($teacher);
        });

        return view('subscriptions.index', [
            'teachers' => $teachers,
        ]);
    }

    /**
     * Grant premium access to a teacher.
     * (Requirement 20.2)
     */
    public function grantPremium(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'expires_at' => ['required', 'date', 'after:today'],
        ]);

        $expiresAt = Carbon::parse($validated['expires_at']);

        $success = $this->subscriptionService->grantPremium(
            $validated['user_id'],
            $expiresAt
        );

        if ($success) {
            return redirect()->route('subscriptions.index')
                ->with('success', 'Premium access granted successfully.');
        }

        return redirect()->route('subscriptions.index')
            ->withErrors(['grant' => 'Failed to grant premium access.']);
    }

    /**
     * Revoke premium access from a teacher.
     * (Requirement 20.3)
     */
    public function revokePremium(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $success = $this->subscriptionService->revokePremium($validated['user_id']);

        if ($success) {
            return redirect()->route('subscriptions.index')
                ->with('success', 'Premium access revoked successfully.');
        }

        return redirect()->route('subscriptions.index')
            ->withErrors(['revoke' => 'Failed to revoke premium access.']);
    }
}

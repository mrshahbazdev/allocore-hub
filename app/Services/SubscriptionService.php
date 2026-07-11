<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\Tool;

class SubscriptionService
{
    public function createTrial(Company $company, ?Plan $plan = null): ?Subscription
    {
        $plan ??= Plan::where('slug', 'growth')->first();

        if (! $plan) {
            return null;
        }

        $trialEndsAt = now()->addDays(14);

        $subscription = Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'status' => Subscription::STATUS_TRIALING,
            'starts_at' => now(),
            'ends_at' => null,
            'trial_ends_at' => $trialEndsAt,
            'payment_method' => 'trial',
        ]);

        $company->update(['trial_ends_at' => $trialEndsAt]);

        $this->syncSubscriptionItems($subscription, $plan);
        $this->syncCompanyTools($company, $subscription);

        return $subscription;
    }

    public function syncSubscriptionItems(Subscription $subscription, Plan $plan): void
    {
        $subscription->items()->delete();

        foreach ($plan->tools as $tool) {
            SubscriptionItem::create([
                'subscription_id' => $subscription->id,
                'item_type' => SubscriptionItem::TYPE_TOOL,
                'item_id' => $tool->id,
                'price' => $tool->pivot->price_override ?? 0,
                'quantity' => $tool->pivot->max_quantity ?? 1,
            ]);
        }

        foreach ($plan->bundles as $bundle) {
            SubscriptionItem::create([
                'subscription_id' => $subscription->id,
                'item_type' => SubscriptionItem::TYPE_BUNDLE,
                'item_id' => $bundle->id,
                'price' => $bundle->pivot->price_override ?? 0,
                'quantity' => 1,
            ]);
        }
    }

    public function syncCompanyTools(Company $company, Subscription $subscription): void
    {
        $company->tools()->detach();

        $status = $subscription->isTrialing() ? 'trialing' : 'active';
        $expiresAt = $subscription->ends_at;

        /** @var Tool $tool */
        foreach ($subscription->plan->tools as $tool) {
            $company->tools()->attach($tool->id, [
                'status' => $status,
                'expires_at' => $expiresAt,
            ]);
        }

        foreach ($subscription->plan->bundles as $bundle) {
            foreach ($bundle->tools as $tool) {
                $company->tools()->attach($tool->id, [
                    'status' => $status,
                    'expires_at' => $expiresAt,
                ]);
            }
        }
    }

    public function isToolAccessible(Company $company, Tool|string $tool): bool
    {
        return $company->hasToolAccess($tool);
    }
}

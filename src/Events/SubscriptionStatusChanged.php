<?php

namespace AngelitoSystems\FilamentTenancy\Events;

use AngelitoSystems\FilamentTenancy\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionStatusChanged
{
    use Dispatchable, SerializesModels;

    public Subscription $subscription;
    public string $oldStatus;
    public string $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Subscription $subscription, string $oldStatus, string $newStatus)
    {
        $this->subscription = $subscription;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}


<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserAddOn;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class SimulateBillingWebhook extends Command
{
    protected $signature = 'billing:simulate
        {type : addon or plan}
        {user : User id}
        {slug : Add-on slug (shop/website) or plan slug (premium/business)}
        {--event=subscription.charged : Razorpay event, e.g. subscription.charged or subscription.cancelled}
        {--sub= : Razorpay subscription id (defaults to the user\'s latest add-on subscription)}
        {--qty=1 : Quantity (shop add-on)}';

    protected $description = 'Testing only: send a signed fake Razorpay subscription webhook to the real webhook handler';

    public function handle(): int
    {
        $secret = config('services.razorpay.webhook_secret');
        if (empty($secret)) {
            $this->error('Webhook secret is not set (admin Payment Settings or RAZORPAY_WEBHOOK_SECRET).');
            return self::FAILURE;
        }

        $type = $this->argument('type');
        $user = User::find($this->argument('user'));
        if (!$user || !in_array($type, ['addon', 'plan'], true)) {
            $this->error('Usage: billing:simulate {addon|plan} {user_id} {slug}');
            return self::FAILURE;
        }

        $slug = $this->argument('slug');
        $subId = $this->option('sub');

        if ($type === 'addon') {
            $notes = ['type' => 'addon', 'user_id' => (string) $user->id, 'addon_slug' => $slug, 'quantity' => (string) $this->option('qty')];
            $subId ??= optional(UserAddOn::where('user_id', $user->id)
                ->whereHas('addOn', fn ($q) => $q->where('slug', $slug))
                ->whereNotNull('razorpay_subscription_id')->latest('id')->first())->razorpay_subscription_id;
        } else {
            $notes = ['type' => 'subscription', 'user_id' => (string) $user->id, 'plan_slug' => $slug];
        }
        $subId ??= 'sub_sim_' . bin2hex(random_bytes(6));

        $payload = ['event' => $this->option('event'), 'payload' => ['subscription' => ['entity' => ['id' => $subId, 'notes' => $notes]]]];
        if ($this->option('event') === 'subscription.charged') {
            $payload['payload']['payment'] = ['entity' => ['id' => 'pay_sim_' . bin2hex(random_bytes(6))]];
        }

        $body = json_encode($payload);
        $request = Request::create('/api/v1/shopowner/razorpay/webhook', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => hash_hmac('sha256', $body, $secret),
        ], $body);

        $response = app(\Illuminate\Contracts\Http\Kernel::class)->handle($request);

        $this->info("Sent {$this->option('event')} for {$type} '{$slug}' (subscription {$subId})");
        $this->line('Webhook response: ' . $response->getStatusCode() . ' ' . $response->getContent());

        if ($type === 'addon') {
            UserAddOn::where('razorpay_subscription_id', $subId)->get()->each(fn ($r) => $this->line(
                "  add-on #{$r->id} status={$r->status} auto_renew=" . ($r->auto_renew ? 'yes' : 'no') . " ends_at={$r->ends_at}"
            ));
        } else {
            $sub = $user->subscriptions()->latest('id')->first();
            $this->line('  subscription: ' . ($sub ? "status={$sub->status} ends_at={$sub->ends_at}" : 'none'));
        }

        return self::SUCCESS;
    }
}

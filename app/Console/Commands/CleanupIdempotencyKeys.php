<?php

namespace App\Console\Commands;

use App\Models\IdempotencyKey;
use Illuminate\Console\Command;

class CleanupIdempotencyKeys extends Command
{
    protected $signature = 'sync:cleanup-idempotency-keys';

    protected $description = 'Delete expired idempotency key records';

    public function handle(): int
    {
        $deleted = IdempotencyKey::where('expires_at', '<', now())->delete();

        $this->info("Deleted {$deleted} expired idempotency key(s).");

        return self::SUCCESS;
    }
}

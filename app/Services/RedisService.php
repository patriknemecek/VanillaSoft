<?php

namespace App\Services;

use App\Utilities\Contracts\RedisHelperInterface;
use Illuminate\Support\Facades\Cache;

class RedisService implements RedisHelperInterface
{
    public function storeRecentMessage(mixed $id, string $messageSubject, string $toEmailAddress): void
    {
        $key = vsprintf('messages.%s', [
            $id
        ]);

        Cache::forever($key, [
            'email_address' => $toEmailAddress,
            'subject' => $messageSubject,
        ]);
    }
}

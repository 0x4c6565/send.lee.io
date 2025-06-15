<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait Expires
{
    const EXPIRES_BURN = -1;
    const EXPIRES_NEVER = 0;

    public function shouldBurn(): bool
    {
        return $this->expires == static::EXPIRES_BURN;
    }

    public function scopeExpired(Builder $query)
    {
        return $query->where(function ($q) {
            $q->where(function ($burnQuery) {
                $burnQuery->where('expires', static::EXPIRES_BURN)
                    ->where('created_at', '<', now()->subDay());
            })->orWhere(function ($timestampQuery) {
                $timestampQuery->where('expires', '>', 0)
                    ->where('expires', '<', now()->timestamp);
            });
        });
    }
}

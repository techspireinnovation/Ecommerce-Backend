<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait BelongsToUser
{
    protected static function bootBelongsToUser(): void
    {
        static::creating(function ($model) {
            if (!empty($model->user_id)) {
                return;
            }
            if (Auth::check()) {
                $model->user_id = Auth::id();
            }
        });
    }
}

<?php

namespace App\Models\Traits;

use App\Scopes\TenantScope;
use App\Models\Business;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBusiness
{
    /**
     * The "booted" method of the model.
     * This automatically applies the TenantScope to any model that uses this trait.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Defines the relationship that every tenant model has with a Business.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
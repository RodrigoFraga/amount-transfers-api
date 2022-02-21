<?php

namespace App\Models\Traits;

use App\Models\Extract;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait ExtractTrait
{
    /**
     * Get the extracts.
     */
    public function extracts (): morphMany
    {
        return $this->morphMany(Extract::class, 'personable');
    }
}

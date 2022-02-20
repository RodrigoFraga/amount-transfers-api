<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Extract extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'value',
        'current_value',
        'type',
    ];


    public function personable (): morphTo
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

use App\Models\Traits\ExtractTrait;
use App\Models\Traits\WalletTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContracts;

class Store extends Model implements AuditableContracts
{
    use HasFactory, SoftDeletes, Auditable, WalletTrait, ExtractTrait;

    protected $fillable = ['name', 'legal_name', 'email', 'document',];

}

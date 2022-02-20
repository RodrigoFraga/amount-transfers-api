<?php

namespace App\Models;

use App\Models\Contracts\TransactionContracts;
use App\Models\Traits\ExtractTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use OwenIt\Auditing\Auditable;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable as AuditableContracts;


class User extends Authenticatable implements AuditableContracts, TransactionContracts
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, ExtractTrait, HasApiTokens, HasRoles, Auditable;

    protected $guard_name = 'api';

    protected $appends = ['role'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'document',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getRoleAttribute ()
    {
        $roles = $this->roles;
        if (count($roles) > 0)
            return strtoupper($roles->first()->name);
        else
            return null;
    }

    /**
     * @Override
     * @return MorphOne
     */
    public function wallet (): morphOne
    {
        return $this->morphOne(Wallet::class, 'personable');
    }


    /**
     * @Override
     * @return HasMany
     */
    public function transactions (): HasMany
    {
        return $this->hasMany(Transaction::class, 'payer_id');
    }
}

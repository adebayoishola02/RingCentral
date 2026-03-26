<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class RingCentralAccount extends Model
{

    /** @use HasFactory<\Database\Factories\RingCentralAccountFactory> */
    use HasFactory;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'company_uuid',
        'created_by_uuid',
        'clint_id',
        'client_secret',
        'server_url',       // production / sandbox
        'refresh_token',    // store encrypted if possible
        'phone_number',
        'access_token',     // usually short-lived, consider not storing
        'expires_at',
        'extension_id',     // ~ for default extension
        'friendly_name',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'metadata'   => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Uuid::uuid4()->toString();
            }
        });
    }

    // Optional relation
    public function messages()
    {
        return $this->hasMany(RingCentralMessage::class, 'ringcentral_account_uuid', 'uuid');
    }
}

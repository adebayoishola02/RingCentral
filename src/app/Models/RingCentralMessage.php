<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class RingCentralMessage extends Model
{
    /** @use HasFactory<\Database\Factories\RingCentralAccountFactory> */
    use HasFactory;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'ringcentral_account_uuid',
        'company_uuid',
        'created_by_uuid',
        'to',
        'from',
        'text',             // RingCentral uses "text" not "body"
        'ringcentral_message_id',   // the ID returned by RingCentral
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
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
}

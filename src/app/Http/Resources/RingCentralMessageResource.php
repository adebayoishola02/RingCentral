<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RingCentralMessageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid'                   => $this->uuid,
            'ringcentral_account_uuid' => $this->ringcentral_account_uuid,
            'to'                     => $this->to,
            'from'                   => $this->from,
            'text'                   => $this->text,
            'status'                 => $this->status,
            'ringcentral_message_id' => $this->ringcentral_message_id,
            'created_at'             => $this->created_at->toIso8601String(),
            'metadata'               => $this->metadata,
        ];
    }
}

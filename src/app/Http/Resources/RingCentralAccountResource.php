<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RingCentralAccountResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid'          => $this->uuid,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'refresh_token' => $this->refresh_token,
            'access_token' => $this->access_token,
            'phone_number' => $this->phone_number,
            'expires_at' => $this->expires_at,
            'friendly_name' => $this->friendly_name,
            'is_active'     => $this->is_active,
            'extension_id'  => $this->extension_id,
            'server_url'    => $this->server_url,
            'metadata'      => $this->metadata,
            'created_at'    => $this->created_at->toIso8601String(),
            // Never expose: client_id, client_secret, refresh_token, access_token
        ];
    }
}

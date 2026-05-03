<?php

namespace App\Services;

use App\Models\RingCentralAccount;
use App\Models\RingCentralMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class RingCentralAccountService
{

    public function getAll($user, array $filters = [])
    {
        $query = RingCentralAccount::where('company_uuid', $user->company_uuid);

        if (!empty($filters['search'])) {
            $query->where('friendly_name', 'like', "%{$filters['search']}%")
                ->orWhere('client_id', 'like', "%{$filters['search']}%");
        }

        $query->orderBy($filters['order_by'] ?? 'created_at', $filters['sort'] ?? 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }


    public function create(array $data, $user): RingCentralAccount
    {
        return DB::transaction(function () use ($data, $user) {

            $account = new RingCentralAccount();
            $account->uuid = Uuid::uuid4()->toString();
            $account->company_uuid = $user->company_uuid;
            $account->created_by_uuid = $user->uuid;
            $account->client_id = $data['client_id'];
            $account->client_secret = $data['client_secret'] ?? null;
            $account->server_url = $data['server_url'] ?? 'https://platform.ringcentral.com';
            $account->access_token = $data['access_token'];
            $account->refresh_token = $data['refresh_token'];
            $account->phone_number = $data['phone_number'];
            $account->extension_id = $data['extension_id'] ?? '~';
            $account->expires_at = $data['expires_at'];
            $account->friendly_name = $data['friendly_name'] ?? null;
            $account->metadata = $data['metadata'] ?? null;
            $account->is_active = true;

            $account->save();

            return $account;
        });
    }

    public function update(array $data, RingCentralAccount $account): RingCentralAccount
    {
        return DB::transaction(function () use ($data, $account) {

            $account->client_id = $data['client_id'] ?? $account->client_id;
            $account->client_secret = $data['client_secret'] ?? $account->client_secret;
            $account->refresh_token = $data['refresh_token'] ?? $account->refresh_token;
            $account->access_token = $data['access_token'] ?? $account->access_token;
            $account->server_url = $data['server_url'] ?? $account->server_url;
            $account->extension_id = $data['extension_id'] ?? $account->extension_id;
            $account->expires_at = $data['expires_at'] ?? $account->expires_at;
            $account->phone_number = $data['phone_number'] ?? $account->phone_number;
            $account->friendly_name = $data['friendly_name'] ?? $account->friendly_name;
            $account->is_active = $data['is_active'] ?? $account->is_active;
            $account->metadata = $data['metadata'] ?? $account->metadata;

            $account->save();

            return $account->fresh();
        });
    }

    public function delete(RingCentralAccount $account): bool
    {
        return DB::transaction(function () use ($account) {

            // Delete All Related Data (Nuclear Option)
            $this->handleRelatedDataOnDisconnect($account);

            // Delete All Related Data (keep records)
            // $this->handleRelatedDataOnDisconnectSoft($account);

            return $account->forceDelete(); // or delete() if soft-deleting
        });
    }

    public static function findByUuid($user, string $uuid): RingCentralAccount
    {
        // Add your authorization logic here (company_uuid match, etc.)
        return RingCentralAccount::where('uuid', $uuid)
            ->where('company_uuid', $user->company_uuid)
            ->firstOrFail();
    }

    // Helper: Get SDK with refreshed token (call before any API usage)
    protected function getAuthenticatedSdk(RingCentralAccount $account): \RingCentral\SDK\SDK
    {
        $rcsdk = new \RingCentral\SDK\SDK(
            $account->client_id,
            $account->client_secret,
            $account->server_url
        );

        $platform = $rcsdk->platform();

        // Refresh if needed
        if ($account->expires_at && $account->expires_at->isPast()) {
            $platform->refresh([
                'refreshToken' => $account->refresh_token,
            ]);

            // Update stored tokens
            // $tokenInfo = $platform->getAccessToken();
            // $account->update([
            //     'access_token' => $tokenInfo->access_token,
            //     'refresh_token' => $tokenInfo->refresh_token, // RingCentral issues new one
            //     'expires_at' => now()->addSeconds($tokenInfo->expires_in),
            // ]);
        } else {
            // $platform->auth()->setToken([
            //     'access_token' => $account->access_token,
            //     'refresh_token' => $account->refresh_token,
            //     'expires_in' => $account->expires_at ? $account->expires_at->diffInSeconds(now()) : 3600,
            // ]);
        }

        return $rcsdk;
    }

    private function handleRelatedDataOnDisconnectSoft(RingCentralAccount $account)
    {
        RingCentralMessage::where('account_uuid', $account->uuid)->update(['account_uuid' => null]);
        Log::info("Ring Central Account disconnected. Data preserved.", [
            'account_uuid' => $account->uuid
        ]);
    }

    private function handleRelatedDataOnDisconnect(RingCentralAccount $account)
    {
        RingCentralMessage::where('account_uuid', $account->uuid)->delete();
        Log::info("Ring Central Account + all related data deleted.", [
            'account_uuid' => $account->uuid
        ]);
    }
}

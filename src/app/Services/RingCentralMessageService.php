<?php

namespace App\Services;

use App\Models\RingCentralAccount;
use App\Models\RingCentralMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RingCentral\SDK\SDK as RingCentralSDK;
use RingCentral\SDK\Http\ApiResponse;
use Exception;
use Ramsey\Uuid\Uuid;

class RingCentralMessageService
{
    public function findByUuid($user, string $uuid): RingCentralMessage
    {
        // Add your authorization logic here (company_uuid match, etc.) 
        return RingCentralMessage::where('uuid', $uuid)
            ->where('company_uuid', $user->company_uuid)
            ->firstOrFail();
    }

    public function getAll($user, array $filters = [])
    {
        $query = RingCentralMessage::where('company_uuid', $user->company_uuid);

        if (!empty($filters['search'])) {
            $query->where('to', 'like', "%{$filters['search']}%")
                ->orWhere('from', 'like', "%{$filters['search']}%")
                ->orWhere('text', 'like', "%{$filters['search']}%");
        }

        $query->orderBy($filters['order_by'] ?? 'created_at', $filters['sort'] ?? 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function sendSms(array $data, $user): RingCentralMessage
    {
        return DB::transaction(function () use ($data, $user) {

            $account = RingCentralAccountService::findByUuid($user, $data['account_uuid'] ?? null);
            $message = new RingCentralMessage();
            $message->uuid = Uuid::uuid4()->toString();
            $message->company_uuid    = $user->company_uuid;
            $message->created_by_uuid = $user->uuid;
            $message->ringcentral_account_uuid = $data['account_uuid'];
            $message->to              = $data['to'];
            $message->from            = $account->phone_number;
            $message->text            = $data['text'];
            $message->status          = 'queued';
            $message->metadata        = $data['metadata'] ?? null;
            $message->save();

            try {
                // Initialize SDK with per-user credentials
                $rcsdk = new RingCentralSDK(
                    $account->client_id,
                    $account->client_secret,
                    $account->server_url ?? 'https://platform.ringcentral.com' // or sandbox
                );

                $platform = $rcsdk->platform();
                $platform->login([
                    'username'     => $account->username ?? null,           // if needed
                    'extension'    => $account->extension_id ?? '~',
                    'password'     => $account->password ?? null,           // rarely used
                    'refreshToken' => $account->refresh_token,
                    // handle token refresh if you store access_token + expires_at
                ]);

                /** @var ApiResponse $resp */
                $resp = $platform->post('/restapi/v1.0/account/~/extension/~/sms', [
                    'from' => ['phoneNumber' => $account->phone_number],
                    'to'   => [['phoneNumber' => $message->to]],
                    'text' => $message->text,
                    // 'statusCallback' not directly supported like Twilio – use webhooks/subscriptions instead
                ]);

                $result = $resp->jsonArray();

                $message->update([
                    'ringcentral_message_id' => $result['id'] ?? null,
                    'status'                 => $result['creationTime'] ? 'sent' : 'queued',
                ]);
            } catch (Exception $e) {
                $message->update(['status' => 'failed']);
                Log::error('RingCentral SMS failed', [
                    'uuid'   => $message->uuid,
                    'error'  => $e->getMessage(),
                ]);
                throw $e;
            }

            return $message;
        });
    }

    // Optional: delete method similar to Twilio
    // public function delete(RingCentralAccount $account) { ... }
}

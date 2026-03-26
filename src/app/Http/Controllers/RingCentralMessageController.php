<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendRingCentralSmsRequest; // create this
use App\Http\Resources\RingCentralMessageCollection;
use App\Http\Resources\RingCentralMessageResource;
use App\Services\RingCentralMessageService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RingCentralMessageController
{
    protected $service;

    public function __construct(RingCentralMessageService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the twilio accounts (paginated).
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'page',
                'per_page',
                'search',
                'order_by',
                'sort',
            ]);

            $records = $this->service->getAll(\Illuminate\Support\Facades\Auth::user(), $filters);

            return response()->json([
                'success' => true,
                'data'    => new RingCentralMessageCollection($records),
                'message' => 'RingCentral messages list retrieved successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve RingCentral messages list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function send(SendRingCentralSmsRequest $request): JsonResponse
    {
        try {
            $message = $this->service->sendSms(
                $request->validated(),
                Auth::user()
            );

            return response()->json([
                'success' => true,
                'data'    => new RingCentralMessageResource($message),
                'message' => 'SMS queued successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            echo $e;
            return response()->json([
                'success' => false,
                'message' => 'RingCentral account not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage(),
            ], 500);
        }
    }
}

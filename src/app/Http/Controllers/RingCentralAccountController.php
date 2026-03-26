<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRingCentralAccountRequest;
use App\Http\Requests\UpdateRingCentralAccountRequest;
use App\Http\Resources\RingCentralAccountCollection;
use App\Http\Resources\RingCentralAccountResource; // create this similar to message resource 
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use App\Services\RingCentralAccountService;
use Exception;
use Illuminate\Http\Request;

class RingCentralAccountController
{
    protected $service;

    public function __construct(RingCentralAccountService $service)
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
                'data'    => new RingCentralAccountCollection($records),
                'message' => 'RingCentral accounts list retrieved successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve RingCentral accounts list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreRingCentralAccountRequest $request): JsonResponse
    {
        try {
            $account = $this->service->create($request->validated(), Auth::user());

            return response()->json([
                'success' => true,
                'data'    => new RingCentralAccountResource($account),
                'message' => 'RingCentral account created successfully.'
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23505' || str_contains($e->getMessage(), 'ring_central_accounts_company_client_unique')) {
                return response()->json([
                    'success' => false,
                    'message' => "A RingCentral account with this client_id and client_secret combination already exists for your company."
                ], 500);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {

            Log::error('Failed to create RingCentral account', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create RingCentral account.'
            ], 500);
        }
    }

    public function update(UpdateRingCentralAccountRequest $request, string $uuid): JsonResponse
    {
        try {
            $account = $this->service->findByUuid(Auth::user(), $uuid); // reuse or add method

            $updated = $this->service->update($request->validated(), $account);

            return response()->json([
                'success' => true,
                'data'    => new RingCentralAccountResource($updated),
                'message' => 'RingCentral account updated successfully.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to update RingCentral account', ['uuid' => $uuid, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Update failed.'], 500);
        }
    }

    public function destroy(string $uuid): JsonResponse
    {
        try {
            $account = $this->service->findByUuid(Auth::user(), $uuid);

            $this->service->delete($account);

            return response()->json([
                'success' => true,
                'message' => 'RingCentral account deleted successfully.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete RingCentral account', ['uuid' => $uuid, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Deletion failed.'], 500);
        }
    }
}

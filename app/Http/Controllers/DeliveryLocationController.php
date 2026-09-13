<?php

namespace App\Http\Controllers;

use App\Contracts\LocationProvider;
use App\Exceptions\OpenRouteServiceException;
use App\Http\Requests\CalculateDeliveryQuoteRequest;
use App\Http\Requests\ReverseDeliveryLocationRequest;
use App\Http\Requests\SearchDeliveryLocationRequest;
use App\Services\DeliveryPricingService;
use Illuminate\Http\JsonResponse;
use Throwable;

class DeliveryLocationController extends Controller
{
    public function __construct(
        private LocationProvider $locationProvider,
        private DeliveryPricingService $deliveryPricingService,
    ) {}

    public function search(SearchDeliveryLocationRequest $request): JsonResponse
    {
        return $this->respond(fn (): array => [
            'locations' => $this->locationProvider->search($request->string('query')->toString()),
        ]);
    }

    public function reverse(ReverseDeliveryLocationRequest $request): JsonResponse
    {
        return $this->respond(fn (): array => [
            'address' => $this->locationProvider->reverse(
                (float) $request->validated('latitude'),
                (float) $request->validated('longitude'),
            ),
        ]);
    }

    public function quote(CalculateDeliveryQuoteRequest $request): JsonResponse
    {
        return $this->respond(fn (): array => $this->deliveryPricingService->quote(
            (float) $request->validated('latitude'),
            (float) $request->validated('longitude'),
            (int) $request->user('customer')->id,
        ));
    }

    private function respond(callable $callback): JsonResponse
    {
        try {
            return response()->json($callback());
        } catch (OpenRouteServiceException $exception) {
            report($exception);

            return response()->json([
                'message' => __('store.validation.messages.delivery_service_unavailable'),
            ], 503);
        } catch (Throwable $exception) {
            throw $exception;
        }
    }
}

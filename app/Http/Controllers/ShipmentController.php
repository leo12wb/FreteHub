<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\UseCases\ConfirmShipment\ConfirmShipmentDTO;
use App\Application\UseCases\ConfirmShipment\ConfirmShipmentUseCase;
use App\Application\UseCases\GetTracking\GetTrackingUseCase;
use App\Http\Requests\ConfirmShipmentRequest;
use App\Http\Resources\ShipmentResource;
use App\Http\Resources\TrackingEventResource;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function __construct(
        private readonly ConfirmShipmentUseCase $confirmShipment,
        private readonly GetTrackingUseCase     $getTracking,
    ) {}

    public function store(ConfirmShipmentRequest $request): JsonResponse
    {
        /** @var Store $store */
        $store = $request->user();

        $dto = new ConfirmShipmentDTO(
            quoteId: $request->string('quote_id')->toString(),
            carrier: $request->string('carrier')->toString(),
            service: $request->string('service')->toString(),
            storeId: (string) $store->id,
        );

        $response = $this->confirmShipment->execute($dto);

        return (new ShipmentResource($response->shipment))
            ->response()
            ->setStatusCode(201);
    }

    public function tracking(Request $request, string $shipmentId): JsonResponse
    {
        /** @var Store $store */
        $store = $request->user();

        $result = $this->getTracking->execute($shipmentId, (string) $store->id);

        return response()->json([
            'shipment' => new ShipmentResource($result['shipment']),
            'events'   => TrackingEventResource::collection($result['events']),
        ]);
    }
}

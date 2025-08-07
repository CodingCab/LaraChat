<?php

namespace App\Http\Controllers\Api;

use App\Abstracts\ShippingServiceAbstract;
use App\Exceptions\ShippingServiceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShippingLabelRequest;
use App\Models\ShippingService;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingLabelController extends Controller
{
    public function store(StoreShippingLabelRequest $request): AnonymousResourceCollection
    {
        try {
            /** @var ShippingService $shippingService */
            $shippingService = ShippingService::query()
                ->where(['code' => $request->validated()['shipping_service_code']])
                ->firstOrFail();

            /** @var ShippingServiceAbstract $shipper */
            $shipper = app($shippingService->service_provider_class);

            $shippingLabelCollection = $shipper->ship($request->validated()['order_id'], $shippingService);

            return JsonResource::collection($shippingLabelCollection);
        } catch (ShippingServiceException $exception) {
            $this->respondBadRequest($exception->getMessage());
        } catch (Exception $exception) {
            report($exception);
            $this->respond503ServiceUnavailable($exception->getMessage());
        }

        return JsonResource::collection([]);
    }
}

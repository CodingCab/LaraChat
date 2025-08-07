<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PacklistOrderIndexRequest;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Reports\src\Models\PacklistReport;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PacklistOrderController extends Controller
{
    public function index(PacklistOrderIndexRequest $request): JsonResource
    {
        // we clear packer ID from other orders first
        Order::query()
            ->where(['packer_user_id' => Auth::guard('api')->id()])
            ->whereNull('packed_at')
            ->get()
            ->each(function (Order $order) {
                $order->update(['packer_user_id' => null]);
            });

        $orderProductList = PacklistReport::json();

        if ($orderProductList['data']->isEmpty()) {
            $this->respondNotFound('There are no more orders to pack with specified filters');
        }

        /** @var OrderProduct $firstOrderProduct */
        $firstOrderProduct = $orderProductList['data'][0];

        ray($firstOrderProduct->toArray());
        /** @var Order $order */
        $order = Order::find($firstOrderProduct->order_id);

        $rowsUpdated = Order::query()
            ->where(['id' => $order->id])
            ->whereNull('packer_user_id')
            ->update(['packer_user_id' => Auth::guard('api')->id()]);

        if ($rowsUpdated === 0) {
            $this->respondBadRequest('Order could not be reserved, try again');
        }

        // we update it once again trough Eloquent for events etc
        $order->update(['packer_user_id' => Auth::guard('api')->id()]);
        $order->log('received order for packing');

        return JsonResource::collection([$order]);
    }
}

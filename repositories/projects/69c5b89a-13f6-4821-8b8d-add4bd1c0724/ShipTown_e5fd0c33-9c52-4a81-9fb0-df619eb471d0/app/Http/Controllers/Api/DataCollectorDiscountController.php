<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Discount\IndexRequest;
use App\Http\Requests\Discount\StoreRequest;
use App\Http\Resources\DiscountsResource;
use App\Modules\DataCollectorDiscounts\src\Models\Discount;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DataCollectorDiscountController extends Controller
{
    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $query = Discount::getSpatieQueryBuilder()->defaultSort('id');

        return DiscountsResource::collection($this->getPaginatedResult($query, 999));
    }

    public function store(StoreRequest $request): DiscountsResource
    {
        $discount = Discount::create($request->validated());

        return new DiscountsResource($discount);
    }

    public function destroy(int $discount_id): DiscountsResource
    {
        $discount = Discount::findOrFail($discount_id);
        $discount->delete();

        return DiscountsResource::make($discount);
    }
}

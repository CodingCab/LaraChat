<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderAddressStoreRequest;
use App\Http\Requests\OrderAddressUpdateRequest;
use App\Http\Resources\OrderAddressResource;
use App\Models\OrderAddress;
use Illuminate\Http\Request;

class OrderAddressController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('filter.search') && $request->input('filter.search') !== null && strlen($request->input('filter.search')) < 3) {
            abort(400, 'Search text must be at least 3 characters long');
        }

        $query = OrderAddress::getSpatieQueryBuilder()
            ->simplePaginate(request()->input('per_page', 10));

        return OrderAddressResource::collection($query);
    }

    public function store(OrderAddressStoreRequest $request)
    {
        $address = OrderAddress::create($request->validated());

        return OrderAddressResource::make($address);
    }

    public function update(OrderAddressUpdateRequest $request, OrderAddress $address): OrderAddressResource
    {
        $address->update($request->validated());

        return OrderAddressResource::make($address);
    }
}

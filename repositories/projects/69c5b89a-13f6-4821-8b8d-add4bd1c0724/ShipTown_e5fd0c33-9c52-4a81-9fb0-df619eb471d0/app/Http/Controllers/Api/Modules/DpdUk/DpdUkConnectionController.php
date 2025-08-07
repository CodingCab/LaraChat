<?php

namespace App\Http\Controllers\Api\Modules\DpdUk;

use App\Http\Controllers\Controller;
use App\Http\Requests\DpdUkConnectionStoreRequest;
use App\Http\Resources\DpdUkConnectionResource;
use App\Models\OrderAddress;
use App\Modules\DpdUk\src\Models\Connection as DpdUkConnection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DpdUkConnectionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = DpdUkConnection::getSpatieQueryBuilder();

        return DpdUkConnectionResource::collection($this->getPaginatedResult($query));
    }

    public function store(DpdUkConnectionStoreRequest $request): DpdUkConnectionResource
    {
        $attributes = $request->validated();
        $attributes['collection_address_id'] = OrderAddress::create($request->validated(['collection_address']))->getKey();

        $connection = DpdUkConnection::create($attributes);

        return DpdUkConnectionResource::make($connection);
    }

    public function destroy(Request $request, int $connection_id): DpdUkConnectionResource
    {
        $connection = DpdUkConnection::findOrFail($connection_id);

        $connection->delete();

        return DpdUkConnectionResource::make($connection);
    }
}

<?php

namespace App\Http\Controllers\Api\Modules\Api2cart;

use App\Http\Controllers\Controller;
use App\Modules\Api2cart\src\Api\Client;
use App\Modules\Api2cart\src\Http\Requests\Api2cartConnectionDestroyRequest;
use App\Modules\Api2cart\src\Http\Requests\Api2cartConnectionIndexRequest;
use App\Modules\Api2cart\src\Http\Requests\Api2cartConnectionStoreRequest;
use App\Modules\Api2cart\src\Jobs\DispatchImportOrdersJobs;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class Api2cartConnectionController extends Controller
{
    public function index(Api2cartConnectionIndexRequest $request): AnonymousResourceCollection
    {
        return JsonResource::collection(Api2cartConnection::all());
    }

    public function store(Api2cartConnectionStoreRequest $request): JsonResource
    {
        $response = Client::POST('', 'account.cart.add.json', $request->validated());

        if (!$response->isSuccess()) {
            abort(400, $response->getReturnMessage());
        }

        $jsonResponse = json_decode($response->getAsJson());

        Api2cartConnection::create([
            'url' => $request->validated('store_url'),
            'bridge_api_key' => data_get($jsonResponse, 'result.store_key')
        ]);
        $config = new Api2cartConnection;
        $config->fill($request->only($config->getFillable()));
        $config->save();

        DispatchImportOrdersJobs::dispatch();

        return new JsonResource($config);
    }

    public function destroy(Api2cartConnectionDestroyRequest $request, Api2cartConnection $connection): Response
    {
        $connection->delete();

        return response('ok');
    }
}

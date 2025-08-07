<?php

namespace App\Http\Controllers\Api\Modules\MagentoApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\MagentoApiConnectionUpdateRequest;
use App\Http\Resources\MagentoConnectionResource;
use App\Models\Configuration;
use App\Modules\Api2cart\src\Api\Client;
use App\Modules\Api2cart\src\Api2cartServiceProvider;
use App\Modules\Api2cart\src\Jobs\DispatchImportOrdersJobs;
use App\Modules\Api2cart\src\Jobs\ImportOrdersJobs;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Services\Api2cartService;
use App\Modules\Magento2API\PriceSync\src\Http\Requests\MagentoApiConnectionDestroyRequest;
use App\Modules\Magento2API\PriceSync\src\Http\Requests\MagentoApiConnectionIndexRequest;
use App\Modules\Magento2API\PriceSync\src\Http\Requests\MagentoApiConnectionStoreRequest;
use App\Modules\Magento2API\PriceSync\src\Models\MagentoConnection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Bus;
use Spatie\Tags\Tag;

class MagentoApiConnectionController extends Controller
{
    public function index(MagentoApiConnectionIndexRequest $request): AnonymousResourceCollection
    {
        $query = MagentoConnection::getSpatieQueryBuilder();

        return MagentoConnectionResource::collection($this->getPaginatedResult($query));
    }

    public function store(MagentoApiConnectionStoreRequest $request): MagentoConnectionResource
    {
        $response = Client::POST('', 'account.cart.add.json', [
            "store_url" => $request->validated('base_url'),
            "cart_id" => "Magento2Api",
            "verify" => true,
            "magento_consumer_key" => $request->validated('consumer_key'),
            "magento_consumer_secret" => $request->validated('consumer_secret'),
            "magento_access_token" => $request->validated('api_access_token'),
            "magento_token_secret" => $request->validated('access_token_secret')
        ]);

        if ($response->isSuccess()) {
            $storeKey = data_get(json_decode($response->getAsJson()), 'result.store_key');
        } else {
            $response = Client::GET('', 'account.cart.list.json', [
                "store_url" => $request->validated('base_url'),
            ]);

            if (!$response->isSuccess()) {
                abort(400, $response->getReturnMessage());
            }

            $storeKey = data_get(json_decode($response->getAsJson()), 'result.carts.0.store_key');
        }

        Api2cartConnection::create([
            'type' => 'Magento2Api',
            'url' => $request->validated('base_url'),
            'bridge_api_key' => $storeKey,
            'enabled' => true,
        ]);

        Api2cartServiceProvider::enableModule();

        $connection = new MagentoConnection;
        $connection->fill($request->only($connection->getFillable()));

        if ($request->has('tag')) {
            $tag = Tag::findOrCreate($request->get('tag'));
            $connection->inventory_source_warehouse_tag_id = $tag->getKey();
        }

        $connection->save();

        Configuration::query()->update(['ecommerce_connected' => true]);

        DispatchImportOrdersJobs::dispatchAfterResponse();

        return new MagentoConnectionResource($connection);
    }

    public function update(MagentoApiConnectionUpdateRequest $request, MagentoConnection $connection): MagentoConnectionResource
    {
        $connection->fill($request->validated());

        if ($request->tag) {
            $tag = Tag::findOrCreate($request->tag);
            $connection->inventory_source_warehouse_tag_id = $tag->id;
            $connection->tags()->sync([$tag->id]);
        }

        $connection->save();

        return new MagentoConnectionResource($connection);
    }

    public function destroy(MagentoApiConnectionDestroyRequest $request, MagentoConnection $connection): MagentoConnectionResource
    {
        $connection->delete();

        return MagentoConnectionResource::make($connection);
    }
}

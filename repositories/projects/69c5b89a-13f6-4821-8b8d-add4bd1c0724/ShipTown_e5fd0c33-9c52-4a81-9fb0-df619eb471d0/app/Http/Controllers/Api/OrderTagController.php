<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderTags\StoreRequest;
use App\Http\Resources\TagResource;
use App\Models\Order;
use App\Models\Taggable;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\QueryBuilder\QueryBuilder;

class OrderTagController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Taggable::class)
            ->allowedSorts(['created_at', 'updated_at', 'tag.name'])
            ->allowedFilters(['taggable_type', 'taggable_id'])
            ->allowedIncludes(['tag']);

        return JsonResource::collection($this->getPaginatedResult($query));
    }

    public function store(StoreRequest $request): AnonymousResourceCollection
    {
        $order = Order::query()->findOrFail($request->validated('order_id'));

        $tags = $request->validated('tags', []);

        $order->syncTags($tags);

        $order->load('tags');

        foreach ($order->tags as $tag) {
            DB::table('taggables')
                ->where('taggable_type', Order::class)
                ->where('taggable_id', $order->id)
                ->where('tag_id', $tag->id)
                ->update(['tag_name' => $tag->name]);
        }

        return TagResource::collection($order->tags);
    }
}

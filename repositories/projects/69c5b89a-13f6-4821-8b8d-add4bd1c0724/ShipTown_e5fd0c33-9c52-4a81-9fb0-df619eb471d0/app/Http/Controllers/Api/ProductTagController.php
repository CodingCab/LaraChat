<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductTags\StoreRequest;
use App\Http\Resources\TagResource;
use App\Models\Product;
use App\Models\Taggable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\QueryBuilder\QueryBuilder;

class ProductTagController extends Controller
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
        $product = Product::findOrFail($request->product_id);

        $tags = $request->tags ?? [];
        $existingTags = $product->tags->pluck('name')->toArray();

        foreach ($tags as $tag) {
            $product->attachTag($tag);
        }

        $tagsToDetach = array_diff($existingTags, $tags);
        if (!empty($tagsToDetach)) {
            $product->detachTags($tagsToDetach);
        }

        return TagResource::collection($product->tags);
    }
}

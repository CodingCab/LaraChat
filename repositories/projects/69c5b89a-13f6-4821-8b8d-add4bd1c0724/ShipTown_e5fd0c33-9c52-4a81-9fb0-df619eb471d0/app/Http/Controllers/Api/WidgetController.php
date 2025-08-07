<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWidgetRequest;
use App\Http\Requests\UpdateWidgetRequest;
use App\Models\Widget;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class WidgetController.
 */
class WidgetController extends Controller
{
    public function store(StoreWidgetRequest $request): JsonResource
    {
        $widget = Widget::create($request->validated());

        return new JsonResource($widget);
    }

    public function update(UpdateWidgetRequest $request, int $widget_id): JsonResource
    {
        $widget = Widget::query()->findOrFail($widget_id);

        $widget->update($request->validated());

        return new JsonResource($widget);
    }
}

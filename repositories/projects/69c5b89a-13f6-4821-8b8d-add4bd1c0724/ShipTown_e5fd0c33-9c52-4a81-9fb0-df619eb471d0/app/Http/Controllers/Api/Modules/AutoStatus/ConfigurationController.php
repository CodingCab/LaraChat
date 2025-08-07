<?php

namespace App\Http\Controllers\Api\Modules\AutoStatus;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutoStatusConfigurationIndexRequest;
use App\Http\Requests\AutoStatusConfigurationStoreRequest;
use App\Http\Resources\AutoStatusConfigurationResource;
use App\Modules\AutoStatusRefill\src\Models\Automation;
use App\Modules\Reports\src\Models\Report;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\QueryBuilder\AllowedFilter;

class ConfigurationController extends Controller
{
    public function index(AutoStatusConfigurationIndexRequest $request): JsonResource
    {
        $report = Report::for(Automation::class);

        $report->addField('ID', 'id');
        $report->addField('From Status Code', 'from_status_code');
        $report->addField('To Status Code', 'to_status_code');
        $report->addField('Desired Order Count', 'desired_order_count', 'numeric');
        $report->addField('Refill Only At 0', 'refill_only_at_0', 'boolean');

        $report->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('from_status_code', "%$value%")
                    ->orWhereLike('to_status_code', "%$value%");
            })
        );

        return $report->toJsonResource();
    }

    public function store(AutoStatusConfigurationStoreRequest $request): JsonResource
    {
        $configuration = Automation::query()
            ->updateOrCreate(['id' => $request->validated('id')], $request->validated());

        return AutoStatusConfigurationResource::make($configuration);
    }

    public function destroy(int $id): JsonResource
    {
        $configuration = Automation::query()->findOrFail($id);

        $configuration->delete();

        return AutoStatusConfigurationResource::collection([$configuration]);
    }
}

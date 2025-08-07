<?php

namespace App\Http\Controllers\Api\Modules\SalesTaxes;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaleTax\StoreRequest;
use App\Http\Requests\SaleTax\UpdateRequest;
use App\Http\Resources\SaleTaxResource;
use App\Modules\Reports\src\Models\SaleTaxReport;
use App\Modules\SalesTaxes\src\Models\SaleTax;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SaleTaxController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $report = new SaleTaxReport;

        $resource = $report->queryBuilder()
            ->simplePaginate(999);

        return SaleTaxResource::collection($resource);
    }

    public function store(StoreRequest $request): SaleTaxResource
    {
        $saleTax = SaleTax::create($request->validated());

        return SaleTaxResource::make($saleTax);
    }

    public function update(UpdateRequest $request, int $saleTaxId): SaleTaxResource
    {
        $saleTax = SaleTax::findOrFail($saleTaxId);

        $saleTax->update($request->validated());

        return SaleTaxResource::make($saleTax);
    }

    public function destroy(int $saleTaxId): SaleTaxResource
    {
        $saleTax = SaleTax::findOrFail($saleTaxId);
        $saleTax->delete();

        return SaleTaxResource::make($saleTax);
    }
}

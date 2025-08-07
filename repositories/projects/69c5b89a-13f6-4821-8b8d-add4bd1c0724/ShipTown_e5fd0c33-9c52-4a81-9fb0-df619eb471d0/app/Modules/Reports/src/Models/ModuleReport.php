<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Module;
use Spatie\QueryBuilder\AllowedFilter;

class ModuleReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Module Index';
        $this->defaultSort = '-name';

        $this->baseQuery = Module::query();

        $this->fields = [
            'id' => 'modules.id',
            'service_provider_class' => 'modules.service_provider_class',
            'enabled' => 'modules.enabled',
            'name' => 'modules.name',
            'description' => 'modules.description',
            'settings_link' => 'modules.settings_link',
        ];

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('modules.name', "$value%");
            })
        );

        $this->addFilter(
            AllowedFilter::callback('search', function ($query, $value) {
                return $query->where('name', 'LIKE', "%$value%");
            })
        );
    }
}

<?php

namespace App\Modules\Reports\src\Models;

use App\Modules\Permissions\src\Models\Permission;
use Spatie\QueryBuilder\AllowedFilter;

class PermissionsReport extends Report
{
    public function __construct()
    {
        parent::__construct();

        $this->report_name = 'Permissions Report';
        $this->defaultSort = 'name';

        $this->baseQuery = Permission::query();

        $this->allowedIncludes = [
            'roles',
        ];

        $this->addField('id');
        $this->addField('name');
        $this->addField('updated_at', type: 'datetime');
        $this->addField('created_at', type: 'datetime');

        $this->addFilter(
            AllowedFilter::callback('search', function ($query, $value) {
                $query->where('name', 'like', '%' . $value . '%');
            })
        );
    }
}

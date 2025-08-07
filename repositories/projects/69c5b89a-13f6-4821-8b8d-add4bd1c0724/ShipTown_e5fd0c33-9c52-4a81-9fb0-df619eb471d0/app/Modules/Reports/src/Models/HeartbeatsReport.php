<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Heartbeat;
use Spatie\QueryBuilder\AllowedFilter;

class HeartbeatsReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = t('Heartbeats');

        $this->baseQuery = Heartbeat::query();

        $this->addFilter(
            AllowedFilter::callback('search', function ($query, $value) {
                $query->where('code', 'like', "%$value%");
            })
        );

        $this->addField('ID', 'heartbeats.id', 'numeric', hidden: false);
        $this->addField('Code', 'heartbeats.code', hidden: false);
        $this->addField('Level', 'heartbeats.level', hidden: false);
        $this->addField('Error Message', 'heartbeats.error_message', hidden: false);
        $this->addField('Auto Heal Job Class', 'heartbeats.auto_heal_job_class');
        $this->addField('Expires At', 'heartbeats.expires_at', 'datetime', hidden: false);
        $this->addField('Created At', 'heartbeats.created_at', 'datetime');
        $this->addField('Updated At', 'heartbeats.updated_at', 'datetime');
    }
}

<?php

namespace App\Modules\Reports\src\Models;

use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;

class ActivityReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Activity Log';
        $this->defaultSort = '-activity_log.id';

        $this->baseQuery = Activity::query()
            ->leftJoin('users', function ($join) {
                $join->on('activity_log.causer_id', '=', 'users.id')
                    ->where('activity_log.causer_type', 'App\User');
            });

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('users.name', "$value%")
                    ->orWhereLike('activity_log.description', "$value%");
            })
        );

        $this->addField('ID', 'activity_log.id', 'numeric', hidden: false);
        $this->addField('Created At', 'activity_log.created_at', 'datetime', hidden: false);
        $this->addField('Description', 'activity_log.description', hidden: false);
        $this->addField('Subject Type', 'activity_log.subject_type', hidden: false);
        $this->addField('Subject ID', 'activity_log.subject_id', 'numeric', hidden: false);
        $this->addField('Causer ID', 'activity_log.causer_id', 'numeric', hidden: false);
        $this->addField('Causer Type', 'activity_log.causer_type', hidden: false);
        $this->addField('User Name', 'users.name', hidden: false);
        $this->addField('Properties', 'activity_log.properties', 'json', hidden: false);

        $this->addAllowedInclude(AllowedInclude::relationship('causer'));
    }
}

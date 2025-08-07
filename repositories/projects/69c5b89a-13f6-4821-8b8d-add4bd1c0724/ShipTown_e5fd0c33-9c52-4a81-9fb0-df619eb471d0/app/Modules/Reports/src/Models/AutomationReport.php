<?php

namespace App\Modules\Reports\src\Models;

use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Conditions\Order\StatusCodeInCondition;
use App\Modules\Automations\src\Models\Automation;
use Spatie\QueryBuilder\AllowedFilter;

class AutomationReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = t('Automations');
        $this->defaultSort = 'priority';

        $this->baseQuery = Automation::query()
            ->leftJoin('modules_automations_conditions as status_from', function ($join) {
                $join->on('modules_automations.id', '=', 'status_from.automation_id')
                    ->whereIn('status_from.condition_class', [
                        StatusCodeInCondition::class, StatusCodeEqualsCondition::class]);
            })
            ->leftJoin('modules_automations_actions as status_code_to', function ($join) {
                $join->on('modules_automations.id', '=', 'status_code_to.automation_id')
                    ->where('status_code_to.action_class', '=', SetStatusCodeAction::class);
            });

        $this->addField('Priority', 'modules_automations.priority', 'numeric', hidden: false);
        $this->addField('Name', 'modules_automations.name', hidden: false);
        $this->addField('From Status Code', 'status_from.condition_value', hidden: false);
        $this->addField('To Status Code', 'status_code_to.action_value', hidden: false);
        $this->addField('Enabled', 'modules_automations.enabled', 'boolean', hidden: false);
        $this->addField('Description', 'modules_automations.description');
        $this->addField('Last Run At', 'modules_automations.last_run_at', 'datetime');
        $this->addField('Created At', 'modules_automations.created_at', 'datetime');
        $this->addField('Updated At', 'modules_automations.updated_at', 'datetime');
        $this->addField('ID', 'modules_automations.id', 'numeric', hidden: false);

        $this->addAllowedInclude('actions');
        $this->addAllowedInclude('conditions');

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('modules_automations.name', "%$value%");
                $query->orWhereLike('status_from.condition_value', "%$value%");
                $query->orWhereLike('status_code_to.action_value', "%$value%");
            })
        );
    }
}

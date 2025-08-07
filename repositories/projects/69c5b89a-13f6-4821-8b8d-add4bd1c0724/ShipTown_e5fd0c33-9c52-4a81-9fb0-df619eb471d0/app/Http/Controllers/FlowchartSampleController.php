<?php

namespace App\Http\Controllers;

use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Conditions\Order\StatusCodeInCondition;
use App\Modules\Automations\src\Models\Automation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FlowchartSampleController extends Controller
{
    public function __invoke(Request $request)
    {
        $records = Automation::query()
            ->leftJoin('modules_automations_conditions as status_from', function ($join) {
                $join->on('modules_automations.id', '=', 'status_from.automation_id')
                    ->whereIn('status_from.condition_class', [
                        StatusCodeInCondition::class, StatusCodeEqualsCondition::class]);
            })
            ->leftJoin('modules_automations_actions as status_to', function ($join) {
                $join->on('modules_automations.id', '=', 'status_to.automation_id')
                    ->where('status_to.action_class', '=', SetStatusCodeAction::class);
            })
            ->select([
                'status_from.condition_value as from_status',
                'status_to.action_value as to_status',
            ])
            ->where('enabled', '=', true)
            ->get();

        $edges = collect();

        foreach ($records as $record) {
            $to = trim((string) $record->to_status);
            if ($to === '') {
                continue;
            }

            $froms = collect(explode(',', (string) $record->from_status))
                ->map(fn ($f) => trim($f))
                ->filter();

            foreach ($froms as $from) {
                $edges->push([$from, $to]);
            }
        }

        $edges = $edges->unique();

        $type = Str::upper($request->get('type', 'TD'));
        if (! in_array($type, ['TD', 'LR', 'RL', 'DT'], true)) {
            $type = 'TD';
        }

        $chart = "flowchart {$type}\n";
        foreach ($edges as $edge) {
            [$from, $to] = $edge;
            $chart .= "    {$from} --> {$to}\n";
        }

        return view('sample-mermaid', ['chart' => $chart]);
    }
}

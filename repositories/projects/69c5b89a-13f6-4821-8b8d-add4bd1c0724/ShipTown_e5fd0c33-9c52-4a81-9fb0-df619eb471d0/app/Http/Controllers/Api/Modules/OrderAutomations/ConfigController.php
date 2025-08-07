<?php

namespace App\Http\Controllers\Api\Modules\OrderAutomations;

use App\Http\Controllers\Controller;
use App\Modules\Automations\src\Http\Requests\AutomationConfigIndexRequest;
use Illuminate\Support\Facades\DB;

class ConfigController extends Controller
{
    public function index(AutomationConfigIndexRequest $request)
    {
        $description = 'Placed in Last 28 Days or Active Orders - Every 5 minutes';

        $conditions = DB::table('modules_automations_available_conditions')
            ->select('class', 'description')
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                return [
                    'class' => $item->class,
                    'description' => $item->description,
                ];
            })->toArray();

        $actions = DB::table('modules_automations_available_actions')
            ->select('class', 'description')
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                return [
                    'class' => $item->class,
                    'description' => $item->description,
                ];
            })->toArray();

        return [
            'description' => $description,
            'conditions' => $conditions,
            'actions' => $actions,
        ];
    }
}

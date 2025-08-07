<?php

namespace App\Http\Controllers\Api\Modules\OrderAutomations;

use App\Http\Controllers\Controller;
use App\Http\Resources\AutomationResource;
use App\Modules\Reports\src\Models\AutomationReport;
use App\Modules\Automations\src\Http\Requests\AutomationDestoyRequest;
use App\Modules\Automations\src\Http\Requests\AutomationIndexRequest;
use App\Modules\Automations\src\Http\Requests\AutomationShowRequest;
use App\Modules\Automations\src\Http\Requests\AutomationStoreRequest;
use App\Modules\Automations\src\Http\Requests\AutomationUpdateRequest;
use App\Modules\Automations\src\Models\Automation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class AutomationController extends Controller
{
    public function index(AutomationIndexRequest $request): JsonResource
    {
        return AutomationReport::json();
    }

    public function store(AutomationStoreRequest $request): AutomationResource
    {
        $automation = new Automation;

        try {
            DB::beginTransaction();

            $automation->fill($request->only(['name', 'event_class', 'enabled', 'priority', 'description']));
            $automation->save();

            // filter out empty conditions and actions
            $conditions = collect($request->validated()['conditions'])
                ->filter(function (array $condition) {
                    return $condition['condition_class'] != '';
                });

            $actions = collect($request->validated()['actions'])
                ->filter(function (array $action) {
                    return $action['action_class'] != '';
                });

            $automation->conditions()->createMany($conditions);
            $automation->actions()->createMany($actions);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            report($e);
            abort(500, 'Something wrong');
        }

        $automation->load(['conditions', 'actions']);

        return new AutomationResource($automation);
    }

    public function show(AutomationShowRequest $request, int $automation_id): AutomationResource
    {
        $automation = Automation::query()
            ->with(['conditions', 'actions'])
            ->findOrFail($automation_id);

        return AutomationResource::make($automation);
    }

    public function update(AutomationUpdateRequest $request, int $automation_id): AutomationResource
    {
        /** @var Automation $automation */
        $automation = Automation::query()->findOrFail($automation_id);

        try {
            DB::beginTransaction();
            $dataAutomation = $request->only(['name', 'event_class', 'enabled', 'priority', 'description']);
            $automation->update($dataAutomation);

            // Delete current data
            $automation->conditions()->delete();
            $automation->actions()->delete();

            // filter out empty conditions and actions
            $conditions = collect($request->validated()['conditions'])
                ->filter(function (array $condition) {
                    return $condition['condition_class'] != '';
                });

            $actions = collect($request->validated()['actions'])
                ->filter(function (array $action) {
                    return $action['action_class'] != '';
                });

            // create conditions and actions
            $automation->conditions()->createMany($conditions);
            $automation->actions()->createMany($actions);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            report($e);
            abort(500, 'Something wrong');
        }

        return new AutomationResource($automation);
    }

    public function destroy(AutomationDestoyRequest $request, int $automation_id): AutomationResource
    {
        $automation = Automation::query()->findOrFail($automation_id);

        $automation->delete();

        return AutomationResource::make($automation);
    }

    public function copy(AutomationShowRequest $request, int $automation_id): AutomationResource
    {
        /** @var Automation $automation */
        $automation = Automation::query()
            ->with(['conditions', 'actions'])
            ->findOrFail($automation_id);

        try {
            DB::beginTransaction();

            $copy = $automation->replicate(['last_run_at', 'created_at', 'updated_at']);

            $copy->enabled = false;
            $copy->name = $automation->name.' copy';

            $copy->save();

            $copy->conditions()->createMany(
                $automation->conditions->map(fn($condition) => [
                    'condition_class' => $condition->condition_class,
                    'condition_value' => $condition->condition_value,
                ])->toArray()
            );

            $copy->actions()->createMany(
                $automation->actions->map(fn($action) => [
                    'priority' => $action->priority,
                    'action_class' => $action->action_class,
                    'action_value' => $action->action_value,
                ])->toArray()
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            abort(500, 'Something wrong');
        }

        $copy->load(['conditions', 'actions']);

        return AutomationResource::make($copy);
    }
}

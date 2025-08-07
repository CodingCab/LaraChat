<?php

namespace App\Modules\Automations\tests\Feature\Conditions;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class ExceptionTestCondition extends BaseOrderConditionAbstract
{
    /**
     * @throws Exception
     */
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        throw new Exception('This exception should be handled by automation');
    }
}

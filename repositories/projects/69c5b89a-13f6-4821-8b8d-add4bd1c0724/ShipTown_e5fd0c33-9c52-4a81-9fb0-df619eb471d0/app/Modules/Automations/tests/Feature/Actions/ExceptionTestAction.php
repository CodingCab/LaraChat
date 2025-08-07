<?php

namespace App\Modules\Automations\tests\Feature\Actions;

use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use Exception;

class ExceptionTestAction extends BaseOrderActionAbstract
{
    /**
     * @throws Exception
     */
    public function handle(string $options = '')
    {
        throw new Exception('This exception should be handled by automation');
    }
}

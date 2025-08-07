<?php

namespace App\Modules\Webhooks\src\Models;

use App\BaseModel;

/**
 * @property string topic_arn
 */
class WebhooksConfiguration extends BaseModel
{
    protected $table = 'modules_webhooks_configuration';

    protected $fillable = [
        'topic_arn',
    ];
}

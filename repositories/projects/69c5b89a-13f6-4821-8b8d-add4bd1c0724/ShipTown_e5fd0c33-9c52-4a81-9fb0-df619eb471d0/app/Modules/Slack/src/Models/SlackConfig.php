<?php

namespace App\Modules\Slack\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $incoming_webhook_url
 */
class SlackConfig extends Model
{
    use HasFactory;

    protected $table = 'modules_slack_config';

    protected $fillable = [
        'incoming_webhook_url',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Conversation
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $message
 * @property string $repository
 * @property string $project_directory
 * @property string|null $claude_session_id
 * @property string|null $filename
 * @property bool $is_processing
 */
class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'repository',
        'project_directory',
        'claude_session_id',
        'filename',
        'is_processing',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

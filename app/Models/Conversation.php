<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}

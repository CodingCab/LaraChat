<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'repository',
        'project_directory',
        'claude_session_id',
        'filename',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

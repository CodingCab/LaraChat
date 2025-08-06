<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'title',
        'repository',
        'project_directory',
        'claude_session_id',
    ];
}

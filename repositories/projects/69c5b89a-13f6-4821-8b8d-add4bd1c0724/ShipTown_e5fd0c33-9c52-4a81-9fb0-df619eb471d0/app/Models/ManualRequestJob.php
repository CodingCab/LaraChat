<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class ManualRequestJob
 *
 * @property int $id
 * @property string $job_name
 * @property string $job_class
 */
class ManualRequestJob extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'job_name',
        'job_class',
    ];
}

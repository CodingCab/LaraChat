<?php

namespace App\Modules\ScheduledReport\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ScheduledReport extends Model
{
    use HasFactory;

    protected $table = 'modules_scheduled_reports';

    protected $fillable = [
        'name',
        'uri',
        'email',
        'cron',
        'next_run_at',
        'created_at',
        'updated_at'
    ];

    protected function casts(): array
    {
        return ['next_run_at' => 'datetime'];
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(ScheduledReport::class)
            ->allowedFilters(['name'])
            ->defaultSort('next_run_at')
            ->allowedSorts([
                'id', 'next_run_at'
            ]);
    }
}

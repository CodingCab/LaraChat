<?php

namespace App\Helpers;

use Closure;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Str;

class TemporaryTable
{
    public static function create($table_name, $subQuery): bool
    {
        DB::statement('DROP TEMPORARY TABLE IF EXISTS '.$table_name);

        $finalQuery = sprintf(
            /** @lang text */
            'CREATE TEMPORARY TABLE %s AS (%s)',
            $table_name,
            $subQuery->toSql()
        );

        return DB::statement($finalQuery, $subQuery->getBindings());
    }

    public static function createEmpty(
        string $table_name,
        string $columnsStatement = 'id bigint(20) unsigned NOT NULL AUTO_INCREMENT'
    ): bool {
        $finalQuery = sprintf(
            /** @lang text */
            'CREATE TEMPORARY TABLE %s (%s)',
            $table_name,
            $columnsStatement
        );

        return DB::statement($finalQuery);
    }

    /**
     * @throws Exception
     */
    public static function fromArray(string $tableName, mixed $data, Closure $blueprint): Builder
    {
        if (! Str::startsWith($tableName, 'tempTable_')) {
            throw new Exception('Temporary table name must start with "tempTable_"');
        }

        Schema::dropIfExists($tableName);

        Schema::create($tableName, $blueprint);

        $table = DB::table($tableName);

        $table->insert($data);

        return $table;
    }
}

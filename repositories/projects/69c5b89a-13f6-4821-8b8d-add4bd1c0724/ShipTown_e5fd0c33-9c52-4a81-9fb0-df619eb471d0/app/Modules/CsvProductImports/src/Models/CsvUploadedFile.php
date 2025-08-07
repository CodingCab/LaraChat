<?php

namespace App\Modules\CsvProductImports\src\Models;

use App\BaseModel;
use Carbon\Carbon;

/**
 * CSV Uploaded File Model.
 *
 * @property int $id
 * @property string $filename
 * @property string $file_content
 * @property array $mapped_fields
 * @property int $processed_records
 * @property int $invalid_records
 * @property Carbon|null $processed_at
 *
 */
class CsvUploadedFile extends BaseModel
{
    protected $table = 'modules_csv_uploaded_files';

    protected array $logAttributes = [
        'filename',
        'mapped_fields',
        'processed_records',
        'invalid_records',
        'processed_at',
    ];

    protected $fillable = [
        'filename',
        'file_content',
        'mapped_fields',
        'processed_records',
        'invalid_records',
        'processed_at',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'mapped_fields' => 'array',
            'processed_records' => 'integer',
            'invalid_records' => 'integer',
            'processed_at' => 'datetime',
        ];
    }
}

<?php

namespace App\Modules\Reports\src\Models;

use App\Helpers\CsvStreamedResponse;
use File;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;

class Report extends ReportBase
{
    public static function jsonResource(): JsonResource
    {
        return (new PermissionsReport())->toJsonResource();
    }

    public static function for(mixed $class): self
    {
        $report1 = new self();

        $report1->baseQuery = $class::query();

        return $report1;
    }

    public function response($request = null): mixed
    {
        $filename = request('filename');
        $extension = $filename ? File::extension($filename) : null;

        return match ($extension) {
            'csv' => CsvStreamedResponse::fromQueryBuilder($this->queryBuilder(), $filename),
            'json' => $this->toJsonResource(),
            default => $this->view(),
        };
    }

    protected function view(): mixed
    {
        try {
            $view = request('view', $this->view);

            return view($view);
        } catch (InvalidFilterQuery $ex) {
            return response($ex->getMessage(), $ex->getStatusCode());
        }
    }

    public function toJsonResource(): JsonResource
    {
        return JsonResource::make([
            'data' => $this->getRecords(),
            'meta' => $this->getMetaData(),
        ]);
    }

    public static function json(array $parameters = []): JsonResource
    {
        $currentRequest = request();
        $newRequest = Request::create(
            $currentRequest->path(),
            $currentRequest->method(),
            array_merge($currentRequest->query(), $parameters)
        );

        app()->instance('request', $newRequest);

        try {
            $report = new static;

            return $report->toJsonResource();
        } finally {
            app()->instance('request', $currentRequest);
        }
    }

    public function addField(string $englishName, mixed $expression = null, string $type = 'string', bool $hidden = true, bool $displayable = true, bool $filterable = true, string $grouping = ''): self
    {
        $allowedCommands = ['string', 'numeric', 'datetime', 'boolean', 'url', 'integer', 'money', 'json'];

        if (!in_array($type, $allowedCommands)) {
            throw new \InvalidArgumentException("'$type' is not a valid data type, allowed data types are: " . implode(', ', $allowedCommands));
        }

        $lowerName = Str::lower(str_replace(' ', '_', $englishName));
        $lowerName = preg_replace('/[^a-z0-9_]/', '_', $lowerName);

        if (!preg_match('/^[a-z]/', $lowerName)) {
            $lowerName = 'c_' . $lowerName;
        }

        $this->allFields[] = [
            'name' => $lowerName,
            'display_name' => Str::replace('_', ' ', t($englishName)),
            'expression' => $expression ?? $lowerName,
            'type' => $type,
            'displayable' => $displayable,
            'filterable' => $filterable,
            'hidden' => $hidden,
            'grouping' => $grouping,
            'visible' => !$hidden,
        ];

        $this->fields[$lowerName] = $expression;
        $this->casts[$lowerName] = in_array($type, ['numeric', 'integer', 'money']) ? 'float' : $type;

        if (! $hidden) {
            $this->defaultSelect[] = $lowerName;
        }

        return $this;
    }

    public function streamCsvFile()
    {
        $stream = fopen('php://temp', 'r+');
        $hasExportedHeaders = false;

        $this->queryBuilder()->chunk(1000, function ($records) use ($stream, &$hasExportedHeaders) {
            if (!$hasExportedHeaders) {
                fputcsv($stream, array_keys($records->first()->toArray()));
                $hasExportedHeaders = true;
            }

            foreach ($records as $record) {
                fputcsv($stream, $record->toArray());
            }
        });

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return $content;
    }
}

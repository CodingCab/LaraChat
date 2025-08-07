<?php

namespace App\Modules\Reports\src\Models;

use App\Models\NavigationMenu;
use App\Traits\HasTagsTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReportBase extends Model
{
    use HasTagsTrait;

    public $table = 'report';

    public string $report_name = 'Report';

    public string $view = 'report-default';

    public array $defaultSelect = [];

    public ?string $defaultSort = null;

    public array $fields = [];

    public array $allFields = [];

    public mixed $baseQuery;

    public array $allowedFilters = [];

    public array $allowedIncludes = [];

    protected array $fieldAliases = [];



    public function baseQuery(): mixed
    {
        return $this->baseQuery;
    }

    public function getRecords(): Collection|array
    {
        $records = $this->getFinalQuery()->get();

        if (empty($this->fields) && $records->isNotEmpty()) {
            $this->fields = $records->first()->toArray();
        }

        return $records;
    }

    public function getFinalQuery(): QueryBuilder
    {
        $limit = request('per_page', 50);
        $page = request('page', 1);

        $offset = ($page - 1) * $limit;

        return $this->queryBuilder()
            ->offset($offset)
            ->limit($limit);
    }

    public function queryBuilder(): QueryBuilder
    {
        $this->fieldAliases = [];

        foreach ($this->fields as $alias => $field) {
            $this->fieldAliases[] = $alias;
        }

        $queryBuilder = QueryBuilder::for($this->baseQuery);

        $queryBuilder = $this->addSelectFields($queryBuilder);

        if ($this->defaultSort) {
            $queryBuilder = $queryBuilder->defaultSort($this->defaultSort);
        }

        $queryBuilder = $queryBuilder
            ->allowedFilters($this->getAllowedFilters())
            ->allowedSorts($this->fieldAliases)
            ->allowedIncludes($this->allowedIncludes);

        $hasGrouping = $this->getVisibleFields()->first(function ($field) {
            return $field['grouping'] !== '';
        });

        if ($hasGrouping) {
            $groupByColumns = $this->getVisibleFields()->filter(function ($field) {
                return $field['grouping'] === '';
            });

            $queryBuilder = $queryBuilder->groupBy($groupByColumns->pluck('expression')->toArray());
        }

        return $queryBuilder;
    }

    public function getMetaData(): array
    {
        $columns = collect($this->allFields);
        
        // Apply column order if specified
        $orderParam = request()->get('order', '');
        if (!empty($orderParam)) {
            $orderedNames = collect(explode(',', $orderParam));
            $columnMap = $columns->keyBy('name');
            
            $orderedColumns = collect();
            
            // Add columns in the specified order
            $orderedNames->each(function ($name) use ($columnMap, &$orderedColumns) {
                if ($columnMap->has($name)) {
                    $orderedColumns->push($columnMap->get($name));
                }
            });
            
            // Add any remaining columns that weren't in the order parameter
            $columnMap->each(function ($column) use ($orderedNames, &$orderedColumns) {
                if (!$orderedNames->contains($column['name'])) {
                    $orderedColumns->push($column);
                }
            });
            
            $columns = $orderedColumns;
        }
        
        return [
            'report_name' => $this->report_name ?? $this->table,
            'pagination' => [
                'per_page' => request('per_page', 50),
                'page' => request('page', 1),
            ],
            'columns' => $columns->values(),
            'actions' => NavigationMenu::query()->where(['group' => request()->uri()->path()])->get(),
            'field_links' => collect(array_keys($this->fields))
                ->map(fn ($field) => [
                    'name' => $field,
                    'display_name' => Str::length($field) > 5 ? Str::headline($field) : Str::upper($field),
                    'type' => $this->getFieldType($field),
                    'operators' => $this->getFieldTypeOperators($field),
                ]),
        ];
    }

    public function addFilter(AllowedFilter $filter): self
    {
        $this->allowedFilters[] = $filter;

        return $this;
    }

    public function addAllowedInclude($include): self
    {
        $this->allowedIncludes[] = $include;

        return $this;
    }

    /**
     * @throws Exception
     */
    private function getAllowedFilters(): array
    {
        $filters = collect($this->allowedFilters);

        $filters = $filters->merge($this->addExactFilters());
        $filters = $filters->merge($this->addContainsFilters());
        $filters = $filters->merge($this->addInFilters());
        $filters = $filters->merge($this->addNotInFilters());
        $filters = $filters->merge($this->addBetweenStringFilters());
        $filters = $filters->merge($this->addBetweenFloatFilters());
        $filters = $filters->merge($this->addBetweenDatesFilters());
        $filters = $filters->merge($this->addGreaterThan());
        $filters = $filters->merge($this->addGreaterThanFloat());
        $filters = $filters->merge($this->addLowerThan());
        $filters = $filters->merge($this->addNullFilters());
        $filters = $filters->merge($this->addNotEqualFilters());
        $filters = $filters->merge($this->addStartsWithFilters());
        $filters = $filters->merge($this->addNotStartsWithFilters());

        return $filters->toArray();
    }

    private function addSelectFields(QueryBuilder $queryBuilder): QueryBuilder
    {
        $requestedSelect = $this->getVisibleFields();

        $requestedSelect
            ->each(function ($field) use ($queryBuilder) {
                if ($field['displayable'] === false) {
                    return;
                }

                $fieldValue = data_get($field, 'expression');

                if ($fieldValue instanceof Expression) {
                    $expression = $fieldValue->getValue($queryBuilder->getGrammar());
                    $queryBuilder->addSelect(DB::raw($field['grouping'] . '('. $expression .') as '. $field['name']));

                    return;
                }

                if ($field['grouping'] !== '') {
                    $queryBuilder->addSelect(DB::raw($field['grouping'].'('.$fieldValue.') as '. $field['name']));
                    return;
                }

                $queryBuilder->addSelect($fieldValue . ' as '. $field['name']);
            });

        return $queryBuilder;
    }

    private function addExactFilters(): array
    {
        $allowedFilters = [];

        // add exact filters
        collect($this->allFields)
            ->filter(function ($field) {
                return data_get($field, 'filterable', false);
            })
            ->each(function ($field) use (&$allowedFilters) {
                $allowedFilters[] = AllowedFilter::callback($field['name'], function ($query, $value) use ($field) {
                    if ($field['grouping'] === '') {
                        return $query->where($field['expression'], '=', $value);
                    } else {
                        return $query->having($field['name'], '=', $value);
                    }
                })->nullable();
            });

        return $allowedFilters;
    }

    private function addContainsFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->filter(function ($value, $key) {
                $type = data_get($this->casts, $key);

                return in_array($type, ['string', null]);
            })
            ->each(function ($record, $alias) use (&$allowedFilters) {
                $filterName = $alias.'_contains';
                if ($record instanceof Expression) {
                    $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($record) {
                        $query->where(new Expression('('.$record->getValue(DB::connection()->getQueryGrammar()).')'), 'LIKE', "%{$value}%");
                    });
                } else {
                    $allowedFilters[] = AllowedFilter::partial($filterName, $record);
                }
            });

        return $allowedFilters;
    }

    private function addBetweenFloatFilters(): array
    {
        $allowedFilters = [];

        collect($this->allFields)
            ->filter(function ($field) {
                return in_array($field['type'], ['numeric', 'float']) && $field['filterable'] === true;
            })
            ->each(function ($field) use (&$allowedFilters) {
                $filterName = $field['name'].'_between';
                $fieldQuery = $field['expression'];

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($fieldQuery, $field) {
                    // we add this to make sure query returns no records if array of two values is not specified
                    if ((! is_array($value)) or (count($value) != 2)) {
                        $query->whereRaw('1=2');

                        return;
                    }

                    if ($fieldQuery instanceof Expression) {
                        $fieldQuery = DB::raw('('.$fieldQuery->getValue($query->getGrammar()).')');
                    }

                    if ($field['grouping'] === '') {
                        $query->whereBetween($fieldQuery, [floatval($value[0]), floatval($value[1])]);
                    } else {
                        $query->havingBetween($field['name'], [floatval($value[0]), floatval($value[1])]);
                    }
                });
            });

        return $allowedFilters;
    }

    private function addNotEqualFilters(): array
    {
        $allowedFilters = [];

        collect($this->allFields)
            ->filter(function ($field) {
                return in_array($field['type'], ['numeric', 'float', 'string']) && $field['filterable'] === true;
            })
            ->each(function ($field) use (&$allowedFilters) {
                $filterName = $field['name'].'_not equal';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($field) {
                    if ($field['grouping'] === '') {
                        return $query->where($field['expression'] ?? $field['name'], '!=', $value);
                    } else {
                        return $query->having($field['name'], '!=', $value);
                    }
                });
            });

        return $allowedFilters;
    }

    /**
     * @throws Exception
     */
    private function addBetweenDatesFilters(): array
    {
        $allowedFilters = [];

        collect($this->allFields)
            ->filter(function ($field) {
                return in_array($field['type'], ['datetime', 'date']) && $field['filterable'] === true;
            })
            ->each(function ($field) use (&$allowedFilters) {
                $filterName = $field['name'].'_between';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($field) {
                    // we add this to make sure query returns no records if array of two values is not specified
                    if ((! is_array($value)) or (count($value) != 2)) {
                        throw new Exception($field['name'].': Invalid filter value, expected array of two values');
                    }


                    if ($field['grouping'] === '') {
                        $expression = $field['expression'];

                        if ($expression instanceof Expression) {
                            $expression = DB::raw('('.$expression->getValue($query->getGrammar()).')');
                        }

                        $query->whereBetween($expression, [Carbon::parse($value[0]), Carbon::parse($value[1])]);
                    } else {
                        $query->havingBetween($field['name'], [Carbon::parse($value[0]), Carbon::parse($value[1])]);
                    }
                });
            });

        return $allowedFilters;
    }

    /**
     * @throws Exception
     */
    private function addGreaterThan(): array
    {
        $allowedFilters = [];

        collect($this->allFields)
            ->filter(function ($field) {
                return in_array($field['type'], ['string', 'datetime']) && $field['filterable'] === true;
            })
            ->each(function ($field) use (&$allowedFilters) {
                $filterName = $field['name'].'_greater_than';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($field) {
                    if ($field['grouping'] === '') {
                        $expression = $field['expression'];

                        if ($expression instanceof Expression) {
                            $expression = DB::raw('('.$expression->getValue($query->getGrammar()).')');
                        }

                        $query->where($expression, '>', $value);
                    } else {
                        $query->having($field['name'], '>', $value);
                    }
                });
            });

        return $allowedFilters;
    }

    /**
     * @throws Exception
     */
    private function addGreaterThanFloat(): array
    {
        $allowedFilters = [];

        collect($this->allFields)
            ->filter(function ($field) {
                return in_array($field['type'], ['float', 'numeric']) && $field['filterable'] === true;
            })
            ->each(function ($field) use (&$allowedFilters) {
                $filterName = $field['name'].'_greater_than';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($field) {
                    if ($field['grouping'] === '') {
                        $expression = $field['expression'];

                        if ($expression instanceof Expression) {
                            $expression = DB::raw('('.$expression->getValue($query->getGrammar()).')');
                        }
                        $query->where($expression, '>', floatval($value));
                    } else {
                        $query->having($field['name'], '>', floatval($value));
                    }
                });
            });

        return $allowedFilters;
    }

    private function addLowerThan(): array
    {
        $allowedFilters = [];

        collect($this->allFields)
            ->filter(function ($field) {
                return in_array($field['type'], ['string', 'datetime', 'float', 'numeric']);
            })
            ->each(function ($field) use (&$allowedFilters) {
                $filterName = $field['name'].'_lower_than';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($field) {
                    if ($field['grouping'] === '') {
                        if (in_array($field['type'], ['float', 'numeric'])) {
                            $query->where($field['expression'], '<', floatval($value));
                        } else {
                            $query->where($field['expression'], '<', $value);
                        }
                    } else {
                        if (in_array($field['type'], ['float', 'numeric'])) {
                            $query->having($field['name'], '<', floatval($value));
                        } else {
                            $query->having($field['name'], '<', $value);
                        }
                    }
                });
            });

        return $allowedFilters;
    }

    private function addNullFilters(): array
    {
        $allowedFilters = [];

        $allowedFilters[] = AllowedFilter::callback('null', function ($query, $value) {
            $query->whereNull($this->fields[$value]);
        });

        return $allowedFilters;
    }

    public function simplePaginatedCollection(): Paginator
    {
        return $this->queryBuilder()->simplePaginate(request()->get('per_page', 10));
    }

    private function addBetweenStringFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->filter(function ($fieldQuery, $fieldName) {
                $type = data_get($this->casts, $fieldName, 'string');

                return $type === 'string';
            })
            ->each(function ($fieldType, $fieldAlias) use (&$allowedFilters) {
                $filterName = $fieldAlias.'_between';
                $fieldQuery = $this->fields[$fieldAlias];

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($fieldQuery) {
                    // we add this to make sure query returns no records if array of two values is not specified
                    if ((! is_array($value)) or (count($value) != 2)) {
                        $query->whereRaw('1=2');

                        return;
                    }

                    if ($fieldQuery instanceof Expression) {
                        $query->whereBetween(DB::raw('('.$fieldQuery->getValue($query->getGrammar()).')'), [floatval($value[0]), floatval($value[1])]);

                        return;
                    }

                    $query->whereBetween($fieldQuery, [$value[0], $value[1]]);
                });
            });

        return $allowedFilters;
    }

    private function addNotInFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->each(function ($type, $alias) use (&$allowedFilters) {
                $filterName = $alias.'_not_in';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($alias) {
                    $query->whereNotIn($this->fields[$alias], explode(',', $value));
                });
            });

        return $allowedFilters;
    }

    private function addInFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->each(function ($type, $alias) use (&$allowedFilters) {
                $filterName = $alias.'_in';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($alias) {
                    $query->whereIn($this->fields[$alias], explode(',', $value));
                });
            });

        return $allowedFilters;
    }

    private function addStartsWithFilters(): array
    {
        $allowedFilters = [];
        collect($this->fields)
            ->filter(function ($value, $key) {
                $type = data_get($this->casts, $key);

                return in_array($type, ['string', 'datetime', 'float', null]);
            })
            ->each(function ($type, $alias) use (&$allowedFilters) {
                $filterName = $alias.'_starts_with';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($alias) {

                    $query->where($this->fields[$alias], 'LIKE', "$value%");
                });
            });

            return $allowedFilters;
    }

    private function addNotStartsWithFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->filter(function ($value, $key) {
                $type = data_get($this->casts, $key);

                return in_array($type, ['string', 'datetime', 'float', null]);
            })
            ->each(function ($type, $alias) use (&$allowedFilters) {
                $filterName = $alias.'_not_starts_with';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($alias) {

                    $query->where($this->fields[$alias], 'NOT LIKE', "$value%");
                });
            });
        return $allowedFilters;
    }

    protected function getFieldType($field): string
    {
        return match ($this->casts[$field] ?? null) {
            'float', 'integer' => 'numeric',
            default => $this->casts[$field] ?? 'string',
        };
    }

    protected function getFieldTypeOperators($field): array
    {
        return match ($this->getFieldType($field)) {
            'string' => ['equals', 'not equal', 'btwn', 'contains', 'greater than', 'lower than', 'starts with', 'not starts with'],
            'numeric' => ['equals', 'not equal', 'btwn', 'greater than', 'lower than'],
            'date' => ['btwn'],
            'datetime' => ['btwn'],
            default => ['contains', 'equals', 'not equal'],
        };
    }

    public function getVisibleFields(): \Illuminate\Support\Collection
    {
        $requestedSelect = collect(explode(',', request()->get('select', '')))->filter();

        if ($requestedSelect->isNotEmpty()) {
            $this->allFields = collect($this->allFields)
                ->map(function ($field) use ($requestedSelect) {
                    $field['visible'] = in_array($field['name'], $requestedSelect->toArray()) && $field['displayable'] === true;
                    return $field;
                })->toArray();
        }

        $requestedSelect = collect($this->allFields)
            ->filter(function ($field) {
                return $field['displayable'] === true && $field['visible'] === true;
            });

        if ($requestedSelect->isEmpty()) {
            $requestedSelect = collect($this->allFields);
        }

        return $requestedSelect;
    }
}

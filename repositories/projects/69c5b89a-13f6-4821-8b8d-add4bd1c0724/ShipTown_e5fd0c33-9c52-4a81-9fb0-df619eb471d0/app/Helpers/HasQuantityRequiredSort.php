<?php

namespace App\Helpers;

class HasQuantityRequiredSort implements \Spatie\QueryBuilder\Sorts\Sort
{
    public function __invoke($query, bool $descending, string $property)
    {
        $query->orderByRaw('(quantity_required IS NULL) ASC');
    }
}

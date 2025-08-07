<?php

namespace App\Actions;

use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateStandardizedBranchName
{
    use AsAction;

    public function handle(string $branch): string
    {
        $withoutDigits = preg_replace('/^\d+-?/', '', $branch);
        $normalized = Str::replace('-', '_', $withoutDigits);

        return Str::limit(Str::lower($normalized), 32, '');
    }
}

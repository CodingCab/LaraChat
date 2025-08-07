<?php

use App\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        User::query()
            ->whereNull('warehouse_code')
            ->withTrashed()
            ->chunk(100, function ($users) {
                User::query()
                    ->whereIn('id', $users->pluck('id'))
                    ->withTrashed()
                    ->update(['warehouse_code' => DB::raw('(SELECT code FROM warehouses WHERE warehouses.id = warehouse_id)')]);
            });
    }
};

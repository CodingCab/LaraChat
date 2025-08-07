<?php

use App\Models\Pick;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('picks', function (Blueprint $table) {
            $table->boolean('is_distributed')->default(false)->after('id');
            // add order_product_ids array column
            $table->json('order_product_ids')->after('quantity_skipped_picking');

            $table->index('is_distributed');
        });

        Pick::query()->where(['is_distributed' => false])->update(['is_distributed' => true]);
    }
};

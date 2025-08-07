<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('pack_quantity')->default(1)->nullable()->change();
            if (!Schema::hasColumn('products', 'product_number')) {
                $table->string('product_number')->default('')->after('supplier_code');
            }
        });

        DB::table('products')->whereNull('pack_quantity')->update(['pack_quantity' => 1]);
    }
};

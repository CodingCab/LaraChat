<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('printers')->nullable()->after('warehouse_id');
        });

        DB::table('users')->select('id', 'printer_id')->get()->each(function ($user) {
            if (!is_null($user->printer_id)) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'printers' => json_encode(['shipping_labels_4x6' => $user->printer_id]),
                    ]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('printer_id');
        });
    }
};

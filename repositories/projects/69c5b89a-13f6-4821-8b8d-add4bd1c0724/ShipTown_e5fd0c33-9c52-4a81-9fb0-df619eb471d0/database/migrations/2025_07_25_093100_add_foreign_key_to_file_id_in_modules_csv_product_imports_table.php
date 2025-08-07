<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if both tables exist
        if (!Schema::hasTable('modules_csv_product_imports') || !Schema::hasTable('modules_csv_uploaded_files')) {
            return;
        }

        // Check if foreign key already exists
        $foreignKeyExists = DB::select("
            SELECT COUNT(*) as count
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'modules_csv_product_imports' 
            AND COLUMN_NAME = 'file_id' 
            AND REFERENCED_TABLE_NAME = 'modules_csv_uploaded_files'
            AND TABLE_SCHEMA = DATABASE()
        ")[0]->count > 0;

        if ($foreignKeyExists) {
            return;
        }
      
        // Check if there are any records with file_id = 0 or invalid file_ids
        $invalidFileIds = DB::table('modules_csv_product_imports')
            ->whereNotNull('file_id')
            ->whereNotIn('file_id', function($query) {
                $query->select('id')->from('modules_csv_uploaded_files');
            })
            ->pluck('file_id')
            ->unique()
            ->toArray();

        // Create a dummy file record if needed
        if (!empty($invalidFileIds) || DB::table('modules_csv_product_imports')->where('file_id', 0)->exists()) {
            $dummyFileId = DB::table('modules_csv_uploaded_files')->insertGetId([
                'filename' => 'seeder_import.csv',
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update any records with file_id = 0 or invalid file_ids to use the dummy file
            DB::table('modules_csv_product_imports')
                ->where('file_id', 0)
                ->orWhereIn('file_id', $invalidFileIds)
                ->update(['file_id' => $dummyFileId]);
        }

        // Now add the foreign key constraint
        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            $table->foreign('file_id')
                ->references('id')
                ->on('modules_csv_uploaded_files')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            $table->dropForeign(['file_id']);
        });
    }
};

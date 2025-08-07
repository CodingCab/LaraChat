<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('orders_addresses', 'document_type')) {
                $table->string('document_type')->nullable()->after('region');
            }

            if (!Schema::hasColumn('orders_addresses', 'document_number_encrypted')) {
                $table->longText('document_number_encrypted')->nullable()->after('document_type');
            }

            if (!Schema::hasColumn('orders_addresses', 'tax_id_encrypted')) {
                $table->longText('tax_id_encrypted')->nullable()->after('document_number_encrypted');
            }

            if (!Schema::hasColumn('orders_addresses', 'tax_id_first_3_chars_md5')) {
                $table->longText('tax_id_first_3_chars_md5')->nullable()->after('tax_id_encrypted');
            }

            if (!Schema::hasColumn('orders_addresses', 'last_name_first_3_chars_md5')) {
                $table->longText('last_name_first_3_chars_md5')->nullable()->after('last_name_encrypted');
            }

            if (Schema::hasColumn('orders_addresses', 'company')) {
                $table->string('company')->nullable()->change();
            }

            if (Schema::hasColumn('orders_addresses', 'gender')) {
                $table->string('gender')->nullable()->change();
            }

            if (Schema::hasColumn('orders_addresses', 'address1')) {
                $table->string('address1')->nullable()->change();
            }

            if (Schema::hasColumn('orders_addresses', 'address2')) {
                $table->string('address2')->nullable()->change();
            }

            if (Schema::hasColumn('orders_addresses', 'postcode')) {
                $table->string('postcode')->nullable()->change();
            }

            if (Schema::hasColumn('orders_addresses', 'city')) {
                $table->string('city')->nullable()->change();
            }

            if (Schema::hasColumn('orders_addresses', 'state_code')) {
                $table->string('state_code')->nullable()->change();
            }

            if (Schema::hasColumn('orders_addresses', 'state_name')) {
                $table->string('state_name')->nullable()->change();
            }

            if (Schema::hasColumn('orders_addresses', 'country_code')) {
                $table->string('country_code')->nullable()->change();
            }

            if (Schema::hasColumn('orders_addresses', 'country_name')) {
                $table->string('country_name')->nullable()->change();
            }

            if (Schema::hasColumn('orders_addresses', 'phone')) {
                $table->string('phone')->nullable()->change();
            }

            if (Schema::hasColumn('orders_addresses', 'fax')) {
                $table->string('fax')->nullable()->change();
            }

            if (Schema::hasColumn('orders_addresses', 'website')) {
                $table->string('website')->nullable()->change();
            }

            if (Schema::hasColumn('orders_addresses', 'region')) {
                $table->string('region')->nullable()->change();
            }
        });


        DB::table('orders_addresses')->select('id', 'last_name_encrypted')->chunkById(100, function ($ordersAddresses) {
            $updates = [];

            foreach ($ordersAddresses as $ordersAddress) {
                try {
                    $decrypted = Crypt::decryptString($ordersAddress->last_name_encrypted);
                    $first3 = substr($decrypted, 0, 3);

                    $updates[] = [
                        'id' => $ordersAddress->id,
                        'last_name_first_3_chars_md5' => md5($first3)
                    ];
                } catch (\Exception $e) {
                    //
                }
            }

            if (!empty($updates)) {
                $cases = [];
                $ids = [];
                foreach ($updates as $update) {
                    $cases[] = "WHEN {$update['id']} THEN '{$update['last_name_first_3_chars_md5']}'";
                    $ids[] = $update['id'];
                }
                $ids = implode(',', $ids);
                $cases = implode(' ', $cases);
                DB::statement("UPDATE orders_addresses SET last_name_first_3_chars_md5 = CASE id {$cases} END WHERE id IN ({$ids})");
            }
        });
    }
};

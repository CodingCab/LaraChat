<?php

use App\Modules\Automations\src\Models\Automation;
use App\Modules\Fakturowo\src\OrderActions\RaiseFakturowoPLInvoice;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $automation = Automation::query()->where('name', '"paid" to "complete"')->first();

        if (!$automation) {
            return;
        }

        $automation->actions()->create([
            'action_class' => RaiseFakturowoPLInvoice::class,
            'action_value' => 'fakturowo_1',
        ]);
    }
};

<?php

namespace Tests\Browser\Components;

use Tests\DuskTestCase;
use Throwable;

class CsvImportModalTest extends DuskTestCase
{
    /**
     * Ensure CSV import modal opens from the new collection modal.
     *
     * @throws Throwable
     */
    public function test_import_csv_modal_opens_from_new_collection(): void
    {
        $this->visit('/products/transfers-in?filter[type]=App\\Models\\DataCollectionTransferIn', $this->testAdmin);

        $this->browser()
            ->script("document.getElementById('app').__vue__.$bvModal.show('new-collection-modal')");

        $this->browser()
            ->waitFor('#new-collection-modal')
            ->click('#import-csv-button')
            ->waitFor('#csv-import-modal')
            ->assertVisible('#csv-import-modal');
    }
}


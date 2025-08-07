<?php

namespace Tests\Browser\Routes\Modules;

use App\Modules\ScheduledReport\src\Models\ScheduledReport;
use App\Modules\ScheduledReport\src\ScheduledReportModulesServiceProvider;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class ScheduledReportsPageTest extends DuskTestCase
{
    private string $uri = '/modules/scheduled-reports';

    /**
     * @throws Throwable
     */
    public function testNoRecordFoundPage()
    {
        ScheduledReport::query()->truncate();

        $this->visit($this->uri);
        $this->browser()
            ->assertPathIs($this->uri)
            ->assertSee('No records found');
    }

    public function testShowListRecordAndFilter()
    {
        $scheduledReports = ScheduledReport::factory(2)->create();

        $this->visit($this->uri);
        $this->browser()
            ->assertDontSee('No records found');

        foreach ($scheduledReports as $scheduledReport) {
            $this->browser()->within('@scheduled-report-' . $scheduledReport->id, function (Browser $browser) use ($scheduledReport) {
                $browser->assertSee($scheduledReport->name)
                    ->assertSee($scheduledReport->email);
            });
        }

        $this->browser()
            ->keys('@barcode-input-field', 'no-record-schedule')
            ->keys('@barcode-input-field', '{enter}')
            ->pause($this->mediumDelay)
            ->assertSee('No records found')
            ->value('@barcode-input-field', '')
            ->keys('@barcode-input-field', 'Sample report')
            ->keys('@barcode-input-field', '{enter}')
            ->pause($this->mediumDelay)
            ->assertDontSee('No records found');
    }

        public function testShowEditAndDeleteModal(): void
    {
        ScheduledReportModulesServiceProvider::enableModule();

        $scheduledReport = ScheduledReport::first() ?? ScheduledReport::factory()->create();

        $this->visit($this->uri);

        $this->say('Opening the edit modal for the scheduled report');
        $this->clickButton('@scheduled-report-' . $scheduledReport->id);

        $this->browser()->waitForText('Edit Scheduled Report');

        $this->say('Closing the edit modal');
//        $this->clickEscape();
//
//        $this->browser()->assertMissing('Edit Scheduled Report');
//
//        $this->say('Deleting the scheduled report');
//        $this->clickButton('@scheduled-report-' . $scheduledReport->id);
//        $this->clickButton('@btn-delete');
//        $this->pause($this->shortDelay);
//
//        $this->browser()->within('.snotify-centerCenter', function ($browser) {
//            $browser->assertSee('Are you sure?')
//                ->click('button:first-of-type')
//                ->pause($this->shortDelay);
//        });
//
//        $this->pause($this->shortDelay);
//        $this->browser()->assertDontSee('New Name');
    }
}

<?php

namespace Tests\Browser\Routes\Tools;

use App\User;
use Tests\DuskTestCase;
use Throwable;

class PrinterPageTest extends DuskTestCase
{
    private string $uri = '/tools/printer';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $browser = $this->browser();
        $browser->disableFitOnFailure();
        $browser->loginAs($user);
        $browser->visit($this->uri);

        $this->startRecording();

        $browser->assertPathIs($this->uri);
        $browser->assertSourceMissing('Server Error');
    }
}

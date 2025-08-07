<?php

namespace Tests\Browser\Routes\Settings;

use Tests\DuskTestCase;
use Throwable;

class UsersPageTest extends DuskTestCase
{
    private string $uri = '/settings/users';

    /**
     * @throws Throwable
     */
    public function testIfPageLoads(): void
    {
        $this->testUser->assignRole('admin');

        $this->visit($this->uri, $this->testUser);
        $this->browser()
                ->pause($this->shortDelay)
                ->assertSee('Users')
                ->assertSee($this->testUser->name);
    }
}

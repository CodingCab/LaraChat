<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use JMac\Testing\Traits\AdditionalAssertions;

abstract class TestCase extends BaseTestCase
{
    use AdditionalAssertions;
    use ResetsDatabase;

    protected User $user;

    public function actingAsUser(): self
    {
        $this->user = User::factory()->create();

        $this->actingAs($this->user, 'web');

        return $this;
    }
}

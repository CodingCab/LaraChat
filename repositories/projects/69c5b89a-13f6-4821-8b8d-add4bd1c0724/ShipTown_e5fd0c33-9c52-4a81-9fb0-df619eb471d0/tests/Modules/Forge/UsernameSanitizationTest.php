<?php

namespace Tests\Modules\Forge;

use App\Actions\FormattedBranchName;
use App\Actions\GenerateStandardizedBranchName;
use PHPUnit\Framework\TestCase as BaseTestCase;

class UsernameSanitizationTest extends BaseTestCase
{
    public function test_username_is_limited_to_32_characters(): void
    {
        $branch = '1234567890Feature-With-Very-Long-Branch-Name-Exceeding-Limit';

        $formatted = FormattedBranchName::run($branch);
        $username = GenerateStandardizedBranchName::run($formatted);

        $this->assertLessThanOrEqual(32, strlen($username));
        $this->assertSame(strtolower($username), $username);
    }
}


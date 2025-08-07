<?php

namespace App\Abstracts;

use Tests\DuskTestCase;

abstract class BrowserTestCase
{
    private Browser $browser;

    protected DuskTestCase $parentTestCase;

    protected function browser(): Browser
    {
        return $this->browser;
    }

    public function setBrowser(Browser $browser): void
    {
        $this->browser = $browser;
    }

    public function setParentTest(DuskTestCase $parentTestCase): void
    {
        $this->parentTestCase = $parentTestCase;
    }
}

<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;

class AssemblyProductsElementsPageTest extends DuskTestCase
{
    private string $uri = '/reports/assembly-products-elements';

    public function testPage(): void
    {
        $this->visit('dashboard');
        $this->visit($this->uri);
    }
}

<?php

namespace App\Modules\Reports\src\Models;

use App\Models\NavigationMenu;

class NavigationMenuReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = t('Navigation Menu');

        $this->baseQuery = NavigationMenu::query();

        $this->addField('ID', 'navigation_menu.id', hidden: false);
        $this->addField('Group', 'navigation_menu.group', hidden: false);
        $this->addField('Name', 'navigation_menu.name', hidden: false);
        $this->addField('Url', 'navigation_menu.url', hidden: false);
    }
}

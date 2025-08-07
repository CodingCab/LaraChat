<?php

namespace App\Observers;

use App\Events\Pick\PickCreatedEvent;
use App\Events\Pick\PickDeletedEvent;

class PickObserver
{
    public function created($pick): void
    {
        PickCreatedEvent::dispatch($pick);
    }

    public function deleted($pick): void
    {
        PickDeletedEvent::dispatch($pick);
    }
}

<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;

class AttachTagsAction extends BaseOrderActionAbstract
{
    public function handle(string $options = ''): bool
    {
        if (trim($options) === '') {
            return true;
        }

        $tags = explode(',', $options);

        $this->order->attachTags($tags);

        return true;
    }
}

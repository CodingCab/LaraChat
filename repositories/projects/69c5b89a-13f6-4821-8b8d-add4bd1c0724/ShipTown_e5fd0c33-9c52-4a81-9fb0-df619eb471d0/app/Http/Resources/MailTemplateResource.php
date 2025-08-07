<?php

namespace App\Http\Resources;

use App\Models\MailTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class MailTemplateResource
 *
 * @package App\Http\Resources
 * @mixin MailTemplate
 */
class MailTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'sender_name' => $this->sender_name,
            'sender_email' => $this->sender_email,
            'name' => $this->name,
            'subject' => $this->subject,
            'reply_to' => $this->reply_to,
            'to' => $this->to,
            'html_template' => $this->html_template,
            'text_template' => $this->text_template,
        ];
    }
}

<?php

namespace App\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MailTemplates\Models\MailTemplate as SpatieMailTemplate;

/**
 * @mixin Eloquent
 *
 * @property string $code
 * @property string $name
 * @property string $sender_email
 * @property string $sender_name
 * @property string $reply_to
 * @property string $subject
 * @property string $to
 * @property string $html_template
 * @property string $text_template
 */
class MailTemplate extends SpatieMailTemplate
{
    use HasFactory;

    protected $fillable = [
        'code',
        'sender_name',
        'sender_email',
        'mailable',
        'subject',
        'html_template',
        'text_template',
        'reply_to',
        'to',
    ];

    public function getNameAttribute()
    {
        return $this->code;
    }
}

<?php

namespace Tests\Feature\Api\MailTemplates;
use PHPUnit\Framework\Attributes\Test;

use App\Mail\ShipmentConfirmationMail;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();

        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->postJson(route('api.mail-templates.store'), [
            'sender_email' => 'a@b.com',
            'sender_name' => 'Sender Name',
            'name' => 'new template',
            'mailable' => ShipmentConfirmationMail::class,
            'code' => 'new_template',
            'subject' => 'New Mail Template',
            'reply_to' => 'reply_to@example.com',
            'to' => 'to@example.com',
            'html_template' => 'test mail',
            'text_template' => 'test mail'
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'subject',
                'reply_to',
                'to',
                'html_template',
                'text_template',
            ],
        ]);
    }
}

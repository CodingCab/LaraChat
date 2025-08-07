<?php

namespace Database\Factories;

use App\Mail\OrderMail;
use Illuminate\Database\Eloquent\Factories\Factory;

class MailTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->word(),
            'sender_name' => $this->faker->name(),
            'sender_email' => $this->faker->safeEmail(),
            'mailable' => OrderMail::class,
            'html_template' => $this->faker->randomHtml(),
        ];
    }
}

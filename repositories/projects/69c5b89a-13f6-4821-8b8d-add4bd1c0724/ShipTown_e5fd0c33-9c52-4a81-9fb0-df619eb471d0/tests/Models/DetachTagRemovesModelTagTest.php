<?php

namespace Tests\Models;

use App\Models\Product;
use Tests\TestCase;

class DetachTagRemovesModelTagTest extends TestCase
{
    public function test_detach_tag_removes_model_tags_record(): void
    {
        /** @var Product $product */
        $product = Product::factory()->create();

        $product->attachTag('TESTTAG');

        $this->assertDatabaseHas('models_tags', [
            'model_type' => Product::class,
            'model_id'   => (string) $product->id,
            'tag_name'   => 'testtag',
            'tag_type'   => null,
        ]);

        $product->detachTag('TESTTAG');

        $this->assertDatabaseMissing('models_tags', [
            'model_type' => Product::class,
            'model_id'   => (string) $product->id,
            'tag_name'   => 'testtag',
            'tag_type'   => null,
        ]);
    }
}


<?php

namespace Tests\Modules\Api2cart\src\Jobs;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Order;
use App\Modules\Api2cart\src\Jobs\ProcessImportedOrdersJob;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use Carbon\Carbon;
use Database\Factories\Modules\Api2cart\src\Models\Api2cartOrderImportsFactory;
use Tests\TestCase;

class ProcessImportedOrdersJobTest extends TestCase
{
    #[Test]
    public function testDpdPolandLockerBoxImport()
    {
        $json_decode = Api2cartOrderImportsFactory::getDpdRawImport();

        Api2cartOrderImports::factory()->create([
            'raw_import' => $json_decode,
        ]);

        ProcessImportedOrdersJob::dispatch();

        /** @var Order $order */
        $order = Order::query()->first();

        $this->assertNotNull($order);

        $this->assertEquals(
            data_get($json_decode, 'additional_fields.dpd_code'),
            $order->shippingAddress->locker_box_code
        );
    }

    #[Test]
    public function testInPostPolandLockerBoxImport()
    {
        $json_decode = Api2cartOrderImportsFactory::getInPostRawImport();

        Api2cartOrderImports::factory()->create([
            'raw_import' => $json_decode,
        ]);

        ProcessImportedOrdersJob::dispatch();

        /** @var Order $order */
        $order = Order::query()->first();

        $this->assertNotNull($order);

        ray($json_decode, $order->shippingAddress);

        $this->assertEquals(
            data_get($json_decode, 'additional_fields.smpaczkomaty.code'),
            $order->shippingAddress->locker_box_code
        );
    }

    #[Test]
    public function testOriginalStatusCodeImport(): void
    {
        $json_decode = Api2cartOrderImportsFactory::new()->getDefaultRawImport();

        Api2cartOrderImports::factory()->create([
            'raw_import' => $json_decode,
        ]);

        ProcessImportedOrdersJob::dispatch();

        /** @var Order $order */
        $order = Order::query()->first();

        $this->assertNotNull($order);
        $this->assertEquals(
            data_get($json_decode, 'status.id'),
            $order->origin_status_code
        );
    }

    #[Test]
    public function testOrderCommentsImport(): void
    {
        $json_decode = Api2cartOrderImportsFactory::new()->getDefaultRawImport();
        $longComment = str_repeat('A', 300);
        $json_decode['comment'] = $longComment;

        Api2cartOrderImports::factory()->create([
            'raw_import' => $json_decode,
        ]);

        ProcessImportedOrdersJob::dispatch();

        /** @var Order $order */
        $order = Order::query()->first();

        $this->assertNotNull($order);
        $this->assertCount(1, $order->orderComments);
        $expectedCreatedAt = Carbon::createFromFormat(
            data_get($json_decode, 'create_at.format'),
            data_get($json_decode, 'create_at.value')
        )->tz('UTC');

        $this->assertEquals(
            $longComment,
            $order->orderComments->first()->comment
        );
        $this->assertSame(300, strlen($order->orderComments->first()->comment));
        $this->assertEquals(
            $expectedCreatedAt->toDateTimeString(),
            $order->orderComments->first()->created_at->toDateTimeString()
        );
        $this->assertTrue($order->orderComments->first()->is_customer);
    }

    #[Test]
    public function testRootLevelCommentImport(): void
    {
        $json_decode = Api2cartOrderImportsFactory::new()->getDefaultRawImport();
        $rootComment = 'This is a root level comment from the order';
        $json_decode['comment'] = $rootComment;

        Api2cartOrderImports::factory()->create([
            'raw_import' => $json_decode,
        ]);

        ProcessImportedOrdersJob::dispatch();

        /** @var Order $order */
        $order = Order::query()->first();

        $this->assertNotNull($order);
        $this->assertCount(1, $order->orderComments);
        
        // Check that the root comment was imported
        $this->assertEquals($rootComment, $order->orderComments->first()->comment);
        
        // Check that the root comment has the order creation date
        $expectedCreatedAt = Carbon::createFromFormat(
            data_get($json_decode, 'create_at.format'),
            data_get($json_decode, 'create_at.value')
        )->tz('UTC');
        
        $this->assertEquals(
            $expectedCreatedAt->toDateTimeString(),
            $order->orderComments->first()->created_at->toDateTimeString()
        );
    }

    #[Test]
    public function testNoCommentImportWhenEmpty(): void
    {
        $json_decode = Api2cartOrderImportsFactory::new()->getDefaultRawImport();
        // Ensure comment is empty or null
        $json_decode['comment'] = null;

        Api2cartOrderImports::factory()->create([
            'raw_import' => $json_decode,
        ]);

        ProcessImportedOrdersJob::dispatch();

        /** @var Order $order */
        $order = Order::query()->first();

        $this->assertNotNull($order);
        $this->assertCount(0, $order->orderComments);
    }
}

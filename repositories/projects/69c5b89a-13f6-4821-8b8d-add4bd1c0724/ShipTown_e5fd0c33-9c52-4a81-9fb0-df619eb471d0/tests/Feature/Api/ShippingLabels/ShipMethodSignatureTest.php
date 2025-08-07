<?php

namespace Tests\Feature\Api\ShippingLabels;

use App\Models\ShippingService;
use App\Modules\Couriers\ShippyPro\Generic\src\Services\GenericService;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

class ShipMethodSignatureTest extends TestCase
{
    #[Test]
    public function ship_method_accepts_nullable_shipping_service(): void
    {
        $method = new ReflectionMethod(GenericService::class, 'ship');
        $param = $method->getParameters()[1];

        $type = $param->getType();
        $this->assertNotNull($type);
        $this->assertTrue($type->allowsNull());
        $this->assertSame(ShippingService::class, $type->getName());
    }
}

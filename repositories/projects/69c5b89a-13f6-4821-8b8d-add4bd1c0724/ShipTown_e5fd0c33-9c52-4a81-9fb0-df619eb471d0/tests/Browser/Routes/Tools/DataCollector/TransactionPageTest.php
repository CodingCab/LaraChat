<?php

namespace Tests\Browser\Routes\Tools\DataCollector;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Warehouse;
use App\Modules\DataCollectorPayments\src\Models\PaymentType;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\BuyXGetYForZPercentDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscountsProduct;
use Tests\DuskTestCase;
use Throwable;

class TransactionPageTest extends DuskTestCase
{
    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        $this->testUser->assignRole('admin');

        $this->browser()
            ->loginAs($this->testUser)
            ->visit('dashboard');

        $this->startRecording('How to sell a product?');

        $this->say('Hi Guys! I will demonstrate how to use our Point Of Sale');
        $this->say('Lets start from basic transaction, from the top menu click on Tools > Point of Sale');

        $this->clickButton('#tools_link');
        $this->clickButton('#point_of_sale_link');

        $this->say('You can see the barcode input field on the left side of the screen ' .
            'where you scan the product using barcode scanner or enter the product code manually');

        $this->type('@barcode-input-field', '4001', true);

        $this->say('Product has been added to the transaction. ' .
            'Scanning product again will increase the quantity');

        $this->type('@barcode-input-field', '4001', true);
        $this->type('@barcode-input-field', '4001', true);

        $this->say('Our Point of Sale system supports multiple payment types, such as cash or card. ' .
            'To accept customers payment, click PAY button on the right side of the screen');

        $this->clickButton('#pay-button');

        $this->say('You can see the payment window, here you can choose the payment type, ' .
            'for this example, we will choose the Cash payment type');

        $this->clickButton('#data-collection-choose-payment-type-modal [data-code="CASH"] div button');

        $this->say('Now you enter the payment amount, and change will be automatically calculated');

        $this->typeSlowly('#transaction_payment_amount', 50);
        $this->clickEnter();

        $this->say('Receipt has been printed and you can now hand it over to the customer together with the change. Click "close" button to serve next customer');

        $this->clickButton('#data-collection-transaction-status-modal-close-button');

        $this->say('Lets start new transaction and I will show you quickly different options which you might need during the transaction');

        $this->clickButton('#start-new-transaction-button');
        $this->clickButton('#options-button');

        $this->say('One of the first options you see is customer selection, lets click "Select Customer" button');

        $this->clickButton('#select-customer-button');

        $this->say('In this window you can create new customer by clicking "plus" button, or find and select existing account');
        $this->clickEscape();
        $this->clickEscape();

        $this->say('Clicking receipt button allows you to print it or email it directly to your customer');
        $this->clickButton('#options-button');
        $this->clickButton('#preview-receipt-button');
        $this->clickEscape();

        $this->say('You also have options cancel current transaction or to put transaction on hold if needed. ' .
            'In the next lesson I will show more advanced features of our Point Of Sale');

        $this->browser()->assertSourceMissing('Server Error');

        $this->stopRecording();
    }

    protected function setUp(): void
    {
        parent::setUp();

        Warehouse::factory()->create();

        $product = Product::factory()->create(['sku' => '4001']);

        ProductPrice::query()->update(['price' => 10]);

        PaymentType::query()->firstOrCreate(['code' => 'cash', 'name' => 'Cash']);

        $quantityDiscount = QuantityDiscount::query()->create([
            'name' => 'Buy 2 Get 1 FREE',
            'job_class' => BuyXGetYForZPercentDiscount::class,
            'configuration' => [
                'quantity_full_price' => 2,
                'quantity_discounted' => 1,
                'discount_percent' => 100,
            ],
        ]);

        QuantityDiscountsProduct::create([
            'quantity_discount_id' => $quantityDiscount->getKey(),
            'product_id' => $product->getKey(),
        ]);
    }
}

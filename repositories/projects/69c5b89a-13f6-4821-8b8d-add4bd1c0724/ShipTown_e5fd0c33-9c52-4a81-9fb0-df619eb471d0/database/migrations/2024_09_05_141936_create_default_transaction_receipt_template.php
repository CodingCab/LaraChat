<?php

use App\Mail\TransactionReceiptMail;
use App\Models\MailTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        MailTemplate::query()->firstOrCreate(['code' => 'transaction_receipt'], [
            'mailable' => TransactionReceiptMail::class,
            'subject' => 'You just completed a #{{ variables.transaction.id }} transaction! Here is your receipt.',
            'html_template' => '',
            'text_template' => '
<esc-center>
<esc-barcode>{{transaction.id}}</esc-barcode>
<esc-font-big>{{store.name}}</esc-font-big>
<esc-font-normal>
{{store.address.address1}}
{{store.address.address2}}
{{store.address.postcode}} {{store.address.city}}
({{store.address.email}})
<esc-br></esc-br>
Trn: #{{transaction.id}} ({{transaction.created_at}})
Cashier: {{transaction.seller}}
</esc-font-normal>
<esc-font-big>DUPLICATE</esc-font-big>
</esc-center>
<esc-left><esc-table-wrap><esc-table><esc-column width="11">Item</esc-column><esc-column width="19">Description</esc-column><esc-column width="6" align="right">Qty</esc-column><esc-column width="6" align="right">Price</esc-column><esc-column width="6" align="right">Amount</esc-column></esc-table>{{#products}}<esc-table><esc-column width="11">{{sku}}</esc-column><esc-column width="19">{{name}}</esc-column><esc-column width="6" align="right">{{quantity}}</esc-column><esc-column width="6" align="right">{{price}}</esc-column><esc-column width="6" align="right">{{total}}</esc-column></esc-table>{{/products}}</esc-table-wrap></esc-left>
<esc-left><esc-table remove-in-preview><esc-column width="36" align="right">Total</esc-column><esc-column width="12" align="right">{{transaction.total}}</esc-column></esc-table></esc-left>
<esc-left><esc-table remove-in-preview><esc-column width="36" align="right">Tendered</esc-column><esc-column width="12" align="right">{{transaction.paid}}</esc-column></esc-table></esc-left>
<esc-left><esc-table remove-in-preview><esc-column width="36" align="right">Change Due</esc-column><esc-column width="12" align="right">{{transaction.change}}</esc-column></esc-table></esc-left>
<esc-center>
Thank you for shopping!
We are happy bo exchange or refund items as sold, accompanied by a valid receipt within 28 days of purchase.
No exchange/refund on underwear, swimwear nets, cut fabrics. For hygienic reasons no exchange/refund on bedding, curtains, make-up or cosmetics if seal opened/removed after purch.
This does not affect your statutory rights.
<esc-br>
<esc-dashed-line></esc-dashed-line>
<esc-bold>Thank you for shopping with us</esc-bold-off>
<esc-br></esc-br>
<esc-font-big>
Shop Online!
<esc-link>{{store.address.website}}</esc-link>
DUPLICATE
</esc-font-big>
<esc-barcode>{{transaction.id}}</esc-barcode>
</esc-center>',
        ]);
    }
};

<?php

use App\Models\MailTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        MailTemplate::query()->create([
            'code' => 'order_confirmation_with_product_list',
            'mailable' => 'App\Mail\OrderMail',
            'subject' => 'Thank you for your order',
            'reply_to' => 'contact@myshiptown.com',
            'html_template' => '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8" />
                    <title>Packing List</title>
                    <style>
                        body {
                            font-family: Verdana, sans-serif;
                            font-size: 12px;
                            background-color: #fff;
                            padding: 20px;
                            margin: 0;
                        }
                        .container {
                            max-width: 650px;
                            margin: 0 auto;
                            border: 1px solid #ccc;
                            padding: 24px;
                        }
                        h1 {
                            font-size: 22px;
                            margin-bottom: 12px;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 15px;
                        }
                        th, td {
                            border: 1px solid #ddd;
                            padding: 8px;
                        }
                        th {
                            background-color: #f5f5f5;
                        }
                        .footer {
                            margin-top: 30px;
                            font-size: 10px;
                            text-align: center;
                            color: #666;
                        }
                    </style>
                </head>
                <body>
                <div class="container">
                    <h1>Thank you for your order!</h1>
                    <p>Order number: <strong>{{ variables.order.order_number }}</strong></p>
                    <p>Issue date: <strong>{{ variables.order.order_placed_at }}</strong></p>
                    <p>Warehouse: <strong>{{ variables.order.packer.warehouse.name }}</strong></p>
                    <p>Status: {{ variables.order.status_code }}</p>
                    <p><strong>Total amount:</strong> {{ variables.order.total_order }} EUR (Products: {{ variables.order.total_products }} EUR, Services: {{ variables.order.total_shipping }} EUR)</p>

                    <table>
                        <tr>
                            <td>
                                <h3>Buyer details</h3>
                                <p>
                                    {{ variables.order.billing_address.first_name }} {{ variables.order.billing_address.last_name }}<br/>
                                    {{ variables.order.billing_address.company }}<br/>
                                    {{ variables.order.billing_address.address1 }}<br />
                                    {{ variables.order.billing_address.address2 }}<br />
                                    {{ variables.order.billing_address.postcode }} {{ variables.order.billing_address.city }} <br/>
                                    {{ variables.order.billing_address.state_name }}<br/>
                                    {{ variables.order.billing_address.country_name }}<br/>
                                    T: {{ variables.order.billing_address.phone }}
                                    <br/>VAT: {{ variables.order.billing_address.tax_id }}
                                </p>
                            </td>
                            <td>
                                <h3>Shipping address</h3>
                                <p>
                                    {{ variables.order.shipping_address.first_name }} {{ variables.order.shipping_address.last_name }}<br/>
                                    {{ variables.order.shipping_address.company }}<br/>
                                    {{ variables.order.shipping_address.address1 }}<br />
                                    {{ variables.order.shipping_address.address2 }}<br />
                                    {{ variables.order.shipping_address.postcode }} {{ variables.order.shipping_address.city }} <br/>
                                    {{ variables.order.shipping_address.state_name }}<br/>
                                    {{ variables.order.shipping_address.country_name }}<br/>
                                    T: {{ variables.order.shipping_address.phone }}<br/>
                                    ID: {{ variables.order.shipping_address.tax_id }}<br/>
                                    Locker Box:{{ variable.order.shipping_address.locker_box_code }}<br/>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <h3>Order items</h3>
                    <table>
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Location</th>
                            <th>Value</th>
                            <th>Shipped</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{#variables.order.order_products}}
                        <tr>
                            <td>{{ variables.order.order_products.sku_ordered }}</td>
                            <td>{{ variables.order.order_products.quantity_ordered }}</td>
                            <td>{{ location }}</td>
                            <td>{{ variables.order.order_products.price }}</td>
                            <td>{{ variables.order.order_products.quantity_shipped }}</td>
                        </tr>
                        {{/variables.order.order_products}}
                        </tbody>
                    </table>

                    <div class="footer">
                        <table>
                            <tr>
                                <td>
                                    Document issued by:<br>
                                    <br><br>
                                    ______________________________ <br>
                                    Signature of the person authorized to issue the document<br>
                                </td>
                                <td>
                                    Document received by:<br>
                                    <br><br>
                                    ______________________________ <br>
                                    Signature of the person authorized to receive the document<br>
                                </td>
                            </tr>
                        </table>

                    </div>
                </div>
                </body>
                </html>'
        ]);
    }
};

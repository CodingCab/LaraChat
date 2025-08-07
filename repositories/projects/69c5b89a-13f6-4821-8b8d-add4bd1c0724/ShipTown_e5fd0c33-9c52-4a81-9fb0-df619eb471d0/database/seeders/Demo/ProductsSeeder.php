<?php

namespace Database\Seeders\Demo;

use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\ProductPicture;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::query()->firstOrCreate(['sku' => '45'], ['name' => 'Test product', 'supplier' => 'ShipTown']);
        ProductPicture::factory()->create(['product_id' => $product->getKey()]);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'en', 'description' => 'This is a test product.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'pl', 'description' => 'To jest testowy produkt.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'hr', 'description' => 'Ovo je testni proizvod.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'es', 'description' => 'Este es un producto de prueba.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'ga', 'description' => 'Is táirge tástála é seo.']);

        $product = Product::query()->firstOrCreate(['sku' => 'X001LE6Q8H', 'name' => 'Eyoyo Clip On Barcode Scanner']);
        ProductPicture::factory()->create(['product_id' => $product->getKey()]);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'en'], ['description' => 'Eyoyo Clip On Barcode Scanner 2D, Portable Barcode Reader with Bluetooth, Data Matrix, 1D, 2D, QR, Android and IOS System']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'pl'], ['description' => 'Eyoyo Clip On Barcode Scanner 2D, Przenośny czytnik kodów kreskowych z Bluetooth, Data Matrix, 1D, 2D, QR, system Android i IOS']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'hr'], ['description' => 'Eyoyo Clip On Barcode Scanner 2D, Prijenosni čitač barkodova s Bluetoothom, Data Matrix, 1D, 2D, QR, Android i IOS sustav']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'es'], ['description' => 'Eyoyo-escáner de código de barras con Clip trasero 2D, lector de código de barras Portátil con Bluetooth, matriz de datos, 1D, 2D, QR, sistema Android e IOS']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'ga'], ['description' => 'Eyoyo Clip On Barcode Scanner 2D, Leabharlann Cód Barraí Portáil le Bluetooth, Maitrís Sonraí, 1D, 2D, QR, Córas Android agus IOS']);


        // Create the Secret Box product
        $product = Product::query()->firstOrCreate(['sku' => '40011', 'name' => 'Secret Box']);
        ProductPicture::factory()->create(['product_id' => $product->getKey()]);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'en'], ['description' => 'A mysterious box that holds secrets.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'pl'], ['description' => 'Tajemnicza skrzynka, która trzyma sekrety.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'hr'], ['description' => 'Tajanstvena kutija koja drži tajne.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'es'], ['description' => 'Una caja misteriosa que guarda secretos.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'ga'], ['description' => 'Bosca mistéire a choinníonn rúin.']);

        // Create the Power Adaptor product
        $product = Product::query()->firstOrCreate(['sku' => '40012', 'name' => 'Power Adaptor']);
        ProductPicture::factory()->create(['product_id' => $product->getKey()]);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'en'], ['description' => 'A versatile power adaptor for various devices.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'pl'], ['description' => 'Uniwersalny zasilacz do różnych urządzeń.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'hr'], ['description' => 'Svestrani adapter za napajanje za razne uređaje.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'es'], ['description' => 'Un adaptador de corriente versátil para varios dispositivos.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'ga'], ['description' => 'Adaptóir cumhachta ilghnóthach do dhéantóirí éagsúla.']);

        // Create the Christmas Snowball product
        $product = Product::query()->firstOrCreate(['sku' => '40013', 'name' => 'Christmas Snowball']);
        ProductPicture::factory()->create(['product_id' => $product->getKey()]);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'en'], ['description' => 'A festive snowball that captures the spirit of Christmas.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'pl'], ['description' => 'Świąteczna kula śnieżna, która uchwyca ducha Bożego Narodzenia.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'hr'], ['description' => 'Festivna snježna kugla koja hvata duh Božića.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'es'], ['description' => 'Una bola de nieve festiva que captura el espíritu de la Navidad.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'ga'], ['description' => 'Bola sneachta bróidne a ghabhann le spiorad na Nollaig.']);

        // Create the Gloves Size L product
        $product = Product::query()->firstOrCreate(['sku' => '40014', 'name' => 'Gloves Size L']);
        ProductPicture::factory()->create(['product_id' => $product->getKey()]);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'en'], ['description' => 'Comfortable gloves, size L, perfect for cold weather.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'pl'], ['description' => 'Wygodne rękawice, rozmiar L, idealne na zimną pogodę.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'hr'], ['description' => 'Udobne rukavice, veličina L, savršene za hladno vrijeme.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'es'], ['description' => 'Guantes cómodos, tamaño L, perfectos para el clima frío.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'ga'], ['description' => 'Léine L, compordach le haghaidh aimsir fuar.']);

        // Create the Buttons 100pk product
        $product = Product::query()->firstOrCreate(['sku' => '40015', 'name' => 'Buttons 100pk']);
        ProductPicture::factory()->create(['product_id' => $product->getKey()]);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'en'], ['description' => 'A pack of 100 assorted buttons for crafting.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'pl'], ['description' => 'Opakowanie 100 różnorodnych guzików do rękodzieła.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'hr'], ['description' => 'Pakiranje od 100 raznih gumba za izradu.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'es'], ['description' => 'Un paquete de 100 botones variados para manualidades.']);
        $product->productDescriptions()->firstOrCreate(['language_code' => 'ga'], ['description' => 'Pacáiste de 100 cnaipe éagsúla le haghaidh ceardaíochta.']);

        // Create T-Shirts
        $tshirtData = [
            ['sku' => '4001', 'name' => 'T-Shirt Blue'],
            ['sku' => '4002', 'name' => 'T-Shirt Brown Grey'],
            ['sku' => '4003', 'name' => 'T-Shirt Light Brown'],
            ['sku' => '4004', 'name' => 'T-Shirt Light Grey'],
            ['sku' => '4005', 'name' => 'T-Shirt Grey'],
            ['sku' => '4006', 'name' => 'T-Shirt Black'],
            ['sku' => '4007', 'name' => 'T-Shirt Purple'],
            ['sku' => '4008', 'name' => 'T-Shirt Green'],
            ['sku' => '4009', 'name' => 'T-Shirt '],
        ];

        foreach ($tshirtData as $tshirt) {
            $product = Product::query()->firstOrCreate($tshirt);
            ProductPicture::factory()->create(['product_id' => $product->getKey()]);
            $product->productDescriptions()->firstOrCreate(['language_code' => 'en'], ['description' => 'High-quality ' . $tshirt['name'] . ' for everyday wear.']);
            $product->productDescriptions()->firstOrCreate(['language_code' => 'pl'], ['description' => 'Wysokiej jakości ' . $tshirt['name'] . ' do codziennego noszenia.']);
            $product->productDescriptions()->firstOrCreate(['language_code' => 'hr'], ['description' => 'Visokokvalitetni ' . $tshirt['name'] . ' za svakodnevno nošenje.']);
            $product->productDescriptions()->firstOrCreate(['language_code' => 'es'], ['description' => 'Camiseta de alta calidad ' . $tshirt['name'] . ' para uso diario.']);
            $product->productDescriptions()->firstOrCreate(['language_code' => 'ga'], ['description' => 'Cámhsát ' . $tshirt['name'] . ' ardchaighdeáin do chaitheamh laethúil.']);
        }
    }

    private function createSkuWithAliases(array $skuList): void
    {
        foreach ($skuList as $sku) {
            if (! Product::query()->where('sku', '=', $sku)->exists()) {
                /** @var Product $product */
                $product = Product::factory()->create(['sku' => $sku]);

                ProductAlias::factory()->create([
                    'product_id' => $product->getKey(),
                    'alias' => $product->sku.'-alias',
                ]);

                ProductAlias::factory()->create([
                    'product_id' => $product->getKey(),
                    'alias' => $product->sku.'a',
                ]);
            }
        }
    }
}

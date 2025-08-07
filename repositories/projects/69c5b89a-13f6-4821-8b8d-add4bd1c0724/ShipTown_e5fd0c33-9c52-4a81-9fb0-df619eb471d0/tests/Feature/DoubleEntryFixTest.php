<?php

namespace Tests\Feature;

use Tests\TestCase;

class DoubleEntryFixTest extends TestCase
{
    /**
     * Test that the fix for double entry is implemented
     * This test verifies that the barcodeFoundManuallyCallback method
     * in BarcodeInputField.vue only calls barcodeScanned without setting the barcode field
     */
    public function testBarcodeInputFieldFixIsImplemented(): void
    {
        // Read the BarcodeInputField.vue file
        $filePath = resource_path('js/components/SharedComponents/BarcodeInputField.vue');
        $content = file_get_contents($filePath);
        
        // Check that the barcodeFoundManuallyCallback method exists
        $this->assertStringContainsString('barcodeFoundManuallyCallback(product)', $content);
        
        // Check that it only calls barcodeScanned and does NOT set this.barcode
        $this->assertStringContainsString("this.barcodeScanned(product['sku']);", $content);
        
        // Ensure the problematic line is NOT present
        $this->assertStringNotContainsString("this.barcode = product['sku'];", $content);
        
        // Verify the method structure looks correct (should be minimal)
        $pattern = '/barcodeFoundManuallyCallback\(product\)\s*{\s*this\.barcodeScanned\(product\[\'sku\'\]\);\s*}/';
        $this->assertMatchesRegularExpression($pattern, $content, 'The barcodeFoundManuallyCallback method should only call barcodeScanned');
    }
}
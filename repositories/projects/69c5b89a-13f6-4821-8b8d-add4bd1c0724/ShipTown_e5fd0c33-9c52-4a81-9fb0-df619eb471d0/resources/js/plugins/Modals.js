const Modals = {
    install(Vue, options) {
        this.EventBus = new Vue()

        Vue.prototype.$modal = {
            show(modal, data) {
                Modals.EventBus.$emit('show::modal::' + modal, data);
            },

            showRecentInventoryMovementsModal(inventory_id) {
                this.show('recent-inventory-movements-modal', {'inventory_id': inventory_id});
            },

            showInventoryReservationsModal(inventory_id) {
                this.show('inventory-reservations-modal', {'inventory_id': inventory_id});
            },

            showDataCollectorQuantityRequestModal(data_collection_id, sku_or_alias, field_name) {
                this.show('data-collector-quantity-request-modal', {
                    'data_collection_id': data_collection_id,
                    'sku_or_alias': sku_or_alias,
                    'field_name': field_name
                });
            },

            showProductDetailsModal(product_id) {
                this.show('product-details-modal', {'product_id': product_id});
            },

            showBarcodeScanner(callback) {
                this.show('barcode-scanner', {'callback': callback});
            },

            showUpsertProductModal(product = null) {
                this.show('new-product-modal', {'product': product});
            },

            showFindProductModal(callback) {
                let called = false;
                this.show('find-product-modal', {
                    'callback': (product) => {
                        if (called) {
                            return;
                        }
                        called = true;
                        callback(product);
                    }
                });
            },

            showAddNewQuantityDiscountModal(discount = null) {
                this.show('new-quantity-discount-modal', {'discount': discount});
            },

            showSetTransactionPrinterModal(printer = null, openTransactionStatusModal = false) {
                this.show('set-transaction-printer-modal', {printer, openTransactionStatusModal});
            },

            showFindAddressModal(data = {type: 'customer'}) {
                this.show('find-address-modal', data);
            },

            showAddNewAddressModal(address = null) {
                this.show('new-address-modal', {'address': address});
            },

            showSetPaymentTypeModal(paymentType = null) {
                this.show('data-collection-choose-payment-type-modal', {'paymentType': paymentType});
            },

            showAddPaymentModal() {
                this.show('data-collection-add-payment-modal');
            },

            showNewPaymentTypeModal(paymentType = null) {
                this.show('module-data-collector-payments-new-payment-type-modal', {'paymentType': paymentType});
            },

            showNewDiscountModal(discount = null) {
                this.show('module-data-collector-discounts-new-discount-modal', {'discount': discount});
            },

            showTransactionStatusModal() {
                this.show('data-collection-transaction-status-modal');
            },

            showImportProductsModal() {
                this.show('import-products-modal');
            },

            showUpdateDataCollectionRecordQuantityModal(details = null) {
                this.show('data-collection-record-update-quantity-modal', {'details': details});
            },

            showUpdateDataCollectionRecordUnitPriceModal(details = null) {
                this.show('data-collection-record-update-unit-price-modal', {'details': details});
            },

            showPreviewTransactionReceiptModal(receiptHtml = null) {
                this.show('data-collection-preview-transaction-receipt-modal', {'receiptHtml': receiptHtml});
            },

            showCreateUpdateSalesTaxModal(id = 0, salesTax = null) {
                this.show('module-sales-taxes-create-update-sales-tax-modal', {'id': id, 'salesTax': salesTax});
            },

            showShelfLocationCommandModal(command, onClosedModal) {
                this.show('set-shelf-location-command-modal', {command, onClosedModal});
            },

            showCreateMailTemplateModal() {
                this.show('create-mail-template-modal');
            },

            showNewConfigurationModal(configuration = null) {
                this.show('module-fakturowo-new-configuration-modal', {'configuration': configuration});
            },

            showAssembleProductQuantityModal(productId = null, disassemble = false) {
                this.show('assemble-product-quantity-modal', {productId, disassemble});
            },

            showSettingsModal() {
                this.show('settings-modal');
            },
        }
    }
}

export default Modals

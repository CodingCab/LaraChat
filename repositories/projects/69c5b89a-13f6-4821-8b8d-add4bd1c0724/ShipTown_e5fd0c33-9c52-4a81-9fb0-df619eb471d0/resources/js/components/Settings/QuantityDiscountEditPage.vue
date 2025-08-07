<template>
    <div v-if="discount">
        <swiping-card disable-swipe-right disable-swipe-left>
            <template v-slot:content>
                <div class="row setting-list">
                    <div class="col-sm-12 col-lg-4">
                        <div class="text-primary">{{ discount['name'] }}</div>
                        <div class="text-secondary small">
                            {{ formatDateTime(discount['created_at'], 'dddd - MMM D HH:mm') }}
                        </div>
                        <div class="text-secondary small">{{ discountTypes[discount.job_class] }}</div>
                    </div>
                    <div class="col-sm-12 col-lg-4">
                        <div class="text-primary">{{ $t('Configuration') }}</div>
                        <template
                            v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\VolumePurchasePercentDiscount' || discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\VolumePurchasePriceDiscount'">
                            <table v-if="sortedQuantityDiscountRanges.length" class="discount-table">
                                <thead>
                                    <tr>
                                        <th>{{ $t('Minimum quantity') }}</th>
                                        <th
                                            v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\VolumePurchasePriceDiscount'">
                                            {{ $t('Discounted price') }}</th>
                                        <th
                                            v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\VolumePurchasePercentDiscount'">
                                            {{ $t('Discount percent') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in sortedQuantityDiscountRanges" :key="row.id">
                                        <td>{{ row.minimum_quantity }}</td>
                                        <td
                                            v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\VolumePurchasePriceDiscount'">
                                            {{ row.discounted_price }}</td>
                                        <td
                                            v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\VolumePurchasePercentDiscount'">
                                            {{ row.discount_percent }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </template>
                        <template v-else>
                            <template
                                v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXGetYForZPriceDiscount' || discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXGetYForZPercentDiscount'">
                                <div class="text-secondary small">
                                    {{ $t('Quantity full price') }}:
                                    {{ dashIfEmpty(discount['configuration']?.quantity_full_price ?? '') }}
                                </div>
                                <div class="text-secondary small">
                                    {{ $t('Quantity discounted') }}:
                                    {{ dashIfEmpty(discount['configuration']?.quantity_discounted ?? '') }}
                                </div>
                            </template>
                            <template
                                v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXGetYForZPriceDiscount'">
                                <div class="text-secondary small">
                                    {{ $t('Discounted price') }}:
                                    {{ dashIfEmpty(discount['configuration']?.discounted_price ?? '') }}
                                </div>
                            </template>
                            <template
                                v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXForYPriceDiscount' || discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXForYPercentDiscount'">
                                <div class="text-secondary small">
                                    {{ $t('Quantity required') }}:
                                    {{ dashIfEmpty(discount['configuration']?.quantity_required ?? '') }}
                                </div>
                            </template>
                            <template
                                v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXForYPriceDiscount'">
                                <div class="text-secondary small">
                                    {{ $t('Discounted unit price') }}:
                                    {{ dashIfEmpty(discount['configuration']?.discounted_unit_price ?? '') }}
                                </div>
                            </template>
                            <template
                                v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXGetYForZPercentDiscount' || discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXForYPercentDiscount'">
                                <div class="text-secondary small">
                                    {{ $t('Discount percent') }}: {{
                                        dashIfEmpty(discount['configuration']?.discount_percent ?? '')
                                    }}
                                </div>
                            </template>
                        </template>
                    </div>
                    <div class="col-sm-12 col-lg-4 text-right">
                        <top-nav-button v-b-modal="'configuration-modal'" icon="edit" />
                    </div>
                </div>
            </template>
        </swiping-card>
        <search-and-option-bar-observer />
        <search-and-option-bar :isStickable="true">
            <div class="d-flex flex-nowrap">
                <div class="flex-fill">
                    <barcode-input-field :input_id="'barcode_input'" placeholder="Enter SKU" ref="barcode" :url_param_name="'search'"
                        :showManualSearchButton="true" @barcodeScanned="addProductToDiscount">
                    </barcode-input-field>
                </div>
            </div>
        </search-and-option-bar>

        <div v-if="(products !== null) && (products.length === 0)" class="text-secondary small text-center mt-3">
            {{ $t('No products found') }}<br>
            {{ $t('Scan or type in SKU to add products to this discount') }}<br>
        </div>

        <template v-if="(products !== null) && (products.length)">
            <swiping-card v-for="product in products" :key="product.id" disable-swipe-right disable-swipe-left>
                <template v-slot:content>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <product-info-card :product="product['product']"></product-info-card>
                        </div>
                        <div class="col-12 col-md-3 text-left small">
                            <div>{{ $t('Price') }}: <strong>{{ displayFullPrice(product) }}</strong></div>
                            <div>{{ $t('Sale price') }}: <strong>{{ displaySalePrice(product) }}</strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-5 d-flex align-items-center justify-content-end">
                            <button class="remove-product d-inline-flex align-items-center justify-content-center"
                                @click="removeProductFromDiscount(product['id'])">
                                <font-awesome-icon icon="trash" class="fa-lg"></font-awesome-icon>
                            </button>
                        </div>
                    </div>
                </template>
            </swiping-card>
        </template>

        <b-modal id="configuration-modal" no-fade hide-header @hidden="setFocusElementById('barcode_input')">
            <ValidationObserver v-slot="{ handleSubmit }">
                <form @submit.prevent="handleSubmit(saveDiscountConfiguration)" ref="loadingContainer">
                    <template
                        v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\VolumePurchasePercentDiscount' || discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\VolumePurchasePriceDiscount'">
                        <div class="setting-list">
                            <div class="setting-body flex-fill">
                                <div class="setting-desc">{{ $t('Click "Add" button to add another quantity range to this discount.') }}
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <b-button variant="primary" type="button" @click="addDiscountRange">{{ $t('Add') }}</b-button>
                            </div>
                        </div>
                        <div v-for="(row, index) in configuration.VolumePurchase_discount_ranges" :key="`row-${index}`"
                            class="d-flex align-items-center VolumePurchase-row">
                            <div class="form-group VolumePurchase-item">
                                <label class="form-label" for="minimum_quantity">{{ $t('Minimum quantity') }}</label>
                                <ValidationProvider vid="minimum_quantity" name="minimum_quantity" v-slot="{ errors }">
                                    <input v-model="row.minimum_quantity" type="number" min="1" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="minimum_quantity" placeholder="1" required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>
                            <div v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\VolumePurchasePercentDiscount'"
                                class="form-group VolumePurchase-item">
                                <label class="form-label" for="discount_percent">{{ $t('Discount Percent') }}</label>
                                <ValidationProvider vid="discount_percent" name="discount_percent" v-slot="{ errors }">
                                    <input v-model="row.discount_percent" type="number" min="1" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="discount_percent" placeholder="1" required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>
                            <div v-else class="form-group VolumePurchase-item">
                                <label class="form-label" for="discounted_price">{{ $t('Discounted Price') }}</label>
                                <ValidationProvider vid="discounted_price" name="discounted_price" v-slot="{ errors }">
                                    <input v-model="row.discounted_price" type="number" min="0" step="0.01" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="discounted_price" placeholder="10" required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>
                            <button class="remove-product VolumePurchase-remove d-inline-flex align-items-center justify-content-center"
                                @click="removeDiscountRange(index)" type="button">
                                <font-awesome-icon icon="trash" class="fa-lg"></font-awesome-icon>
                            </button>
                        </div>
                    </template>
                    <template v-else>
                        <template
                            v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXGetYForZPriceDiscount' || discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXGetYForZPercentDiscount'">
                            <div class="form-group">
                                <label class="form-label" for="quantity_full_price">{{ $t('Quantity Full Price') }}</label>
                                <ValidationProvider vid="quantity_full_price" name="quantity_full_price" v-slot="{ errors }">
                                    <input v-model="configuration.quantity_full_price" type="number" min="1" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="quantity_full_price" placeholder="1" required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="quantity_discounted">{{ $t('Quantity Discounted') }}</label>
                                <ValidationProvider vid="quantity_discounted" name="quantity_discounted" v-slot="{ errors }">
                                    <input v-model="configuration.quantity_discounted" type="number" min="1" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="quantity_discounted" placeholder="1" required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>
                        </template>
                        <template
                            v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXGetYForZPriceDiscount'">
                            <div class="form-group">
                                <label class="form-label" for="discounted_price">{{ $t('Discounted Price') }}</label>
                                <ValidationProvider vid="discounted_price" name="discounted_price" v-slot="{ errors }">
                                    <input v-model="configuration.discounted_price" type="number" min="0" step="0.01" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="discounted_price" placeholder="10" required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>
                        </template>
                        <template
                            v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXForYPriceDiscount' || discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXForYPercentDiscount'">
                            <div class="form-group">
                                <label class="form-label" for="quantity_required">{{ $t('Quantity Required') }}</label>
                                <ValidationProvider vid="quantity_required" name="quantity_required" v-slot="{ errors }">
                                    <input v-model="configuration.quantity_required" type="number" min="1" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="quantity_required" placeholder="1" required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>
                        </template>
                        <template
                            v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXForYPriceDiscount'">
                            <div class="form-group">
                                <label class="form-label" for="discounted_unit_price">{{ $t('Discounted Unit Price') }}</label>
                                <ValidationProvider vid="discounted_unit_price" name="discounted_unit_price" v-slot="{ errors }">
                                    <input v-model="configuration.discounted_unit_price" type="number" min="0" step="0.01" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="discounted_unit_price" placeholder="1" required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>
                        </template>
                        <template
                            v-if="discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXGetYForZPercentDiscount' || discount.job_class === 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXForYPercentDiscount'">
                            <div class="form-group">
                                <label class="form-label" for="discount_percent">{{ $t('Discount Percent') }}</label>
                                <ValidationProvider vid="discount_percent" name="discount_percent" v-slot="{ errors }">
                                    <input v-model="configuration.discount_percent" type="number" min="0" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="discount_percent" placeholder="10" required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>
                        </template>
                    </template>
                    <div class="d-flex justify-content-end">
                        <b-button variant="secondary" type="button" @click="$bvModal.hide('configuration-modal');" class="mr-2">
                            {{ $t('Cancel') }}
                        </b-button>
                        <b-button variant="primary" type="submit">{{ $t('Save') }}</b-button>
                    </div>
                </form>
            </ValidationObserver>
            <template #modal-footer>
                <div></div>
            </template>
        </b-modal>
    </div>
</template>

<script>
import api from "../../mixins/api.vue";
import helpers from "../../mixins/helpers";
import SwipingCard from "../SharedComponents/SwipingCard.vue";
import loadingOverlay from "../../mixins/loading-overlay";
import beep from "../../mixins/beep";
import url from "../../mixins/url.vue";
import { ValidationObserver, ValidationProvider } from "vee-validate";
import Breadcrumbs from "../Reports/Breadcrumbs.vue";

export default {
    mixins: [loadingOverlay, beep, url, api, helpers],

    components: {
        Breadcrumbs,
        SwipingCard,
        ValidationObserver,
        ValidationProvider
    },

    props: {
        initialDiscount: {
            type: String,
            default: null
        },
    },

    data() {
        return {
            discount: null,
            products: null,
            configuration: {
                VolumePurchase_discount_ranges: [],
                quantity_full_price: null,
                quantity_discounted: null,
                discounted_price: null,
                discount_percent: null,
                quantity_required: null,
                discounted_unit_price: null
            },
            discountTypes: {
                'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXGetYForZPriceDiscount': this.$t('Buy X, get Y for Z price'),
                'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXGetYForZPercentDiscount': this.$t('Buy X, get Y for Z percent discount'),
                'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXForYPriceDiscount': this.$t('Buy X for Y price'),
                'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXForYPercentDiscount': this.$t('Buy X for Y percent discount'),
                'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\VolumePurchasePercentDiscount': this.$t('VolumePurchase percent discount'),
                'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\VolumePurchasePriceDiscount': this.$t('VolumePurchase price discount')
            },
        }
    },

    mounted() {
        if (this.initialDiscount) {
            this.discount = JSON.parse(this.initialDiscount);
            this.discount = { ...this.discount, configuration: this.discount.configuration };
            this.configuration = { ...this.configuration, ...this.discount.configuration };
        }

        this.reloadQuantityDiscountProducts();
    },

    computed: {
        sortedQuantityDiscountRanges() {
            return [...this.configuration.VolumePurchase_discount_ranges].sort((a, b) => a.minimum_quantity - b.minimum_quantity);
        },
    },

    methods: {
        reloadQuantityDiscountProducts() {
            let params = {
                'filter[quantity_discount_id]': this.discount.id,
                'include': 'product,product.prices',
                'sort': '-updated_at'
            };

            this.apiGetQuantityDiscountProduct(params)
                .then(response => {
                    this.products = response.data.data;
                })
                .catch(error => {
                    this.displayApiCallError(error);
                });
        },

        addProductToDiscount(barcode) {
            if (barcode.trim() === '') {
                return;
            }

            this.apiGetProducts({ 'filter[sku_or_alias]': barcode, 'include': 'prices' })
                .then(response => {
                    if (response.data.data.length === 0) {
                        this.notifyError(this.$t('Product {barcode} not found.', { barcode }));
                        return;
                    }

                    const product = response.data.data[0];

                    if (this.products === null) {
                        this.products = [];
                    }

                    if (!this.products.some(p => p.product_id === product.id)) {
                        this.apiPostQuantityDiscountProduct({
                            quantity_discount_id: this.discount.id,
                            product_id: product.id
                        }).then(() => {
                            this.reloadQuantityDiscountProducts();
                            this.notifySuccess(this.$t('Product added to discount.'));
                        }).catch(error => {
                            this.displayApiCallError(error);
                        });
                    } else {
                        this.notifyError(this.$t(`Product {barcode} is already added to this discount.`, { barcode }));
                    }
                })
                .catch(error => {
                    this.displayApiCallError(error);
                });
        },

        removeProductFromDiscount(discountProductId) {
            this.apiRemoveQuantityDiscountProduct(discountProductId)
                .then(response => {
                    if (typeof response.data !== 'undefined' && typeof response.data !== 'undefined') {
                        this.reloadQuantityDiscountProducts();
                        this.notifySuccess(this.$t('Product removed from discount.'));
                    }
                })
                .catch(error => {
                    this.displayApiCallError(error);
                });
        },

        saveDiscountConfiguration() {
            this.showLoading();
            const config = Object.fromEntries(Object.entries(this.configuration).filter(([_, v]) => v !== null));
            this.apiPutQuantityDiscount(this.discount.id, { ...this.discount, configuration: config })
                .then(response => {
                    if (typeof response.data !== 'undefined' && typeof response.data.data !== 'undefined') {
                        this.discount = {
                            ...response.data.data,
                            configuration: response.data.data.configuration
                        };
                        this.configuration = { ...this.configuration, ...this.discount.configuration };
                        this.notifySuccess(this.$t('Discount configuration saved.'));
                    }
                })
                .catch(error => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.hideLoading();
                    this.$bvModal.hide('configuration-modal');
                });
        },

        displayFullPrice(product) {
            if (this.currentUser()['warehouse_code']) {
                return this.dashIfZero(product.product.prices[this.currentUser()['warehouse_code']].price);
            } else {
                return this.dashIfZero(product.product.price);
            }
        },

        displaySalePrice(product) {
            if (this.currentUser()['warehouse_code']) {
                return this.dashIfZero(product.product.prices[this.currentUser()['warehouse_code']]['current_price']);
            } else {
                return this.dashIfZero(product.product['sale_price']);
            }
        },

        addDiscountRange() {
            this.configuration.VolumePurchase_discount_ranges.push({
                minimum_quantity: null,
                discounted_price: null,
                discount_percent: null
            });
        },

        removeDiscountRange(index) {
            this.configuration.VolumePurchase_discount_ranges.splice(index, 1);
        }
    }
}
</script>

<style lang="scss" scoped>
.remove-product {
    cursor: pointer;
    width: 40px;
    height: 40px;
    border: 1px solid #dc3545;
    color: #dc3545;
    background-color: transparent;
    transition: background-color 0.3s, color 0.3s;

    &:hover {
        background-color: #dc3545;
        color: white;
    }
}

.VolumePurchase-row {
    gap: 10px;
}

.VolumePurchase-item {
    flex: 1;
}

.VolumePurchase-remove {
    margin-top: 15px;
    flex: 0 0 37px;
    max-width: 37px;
    height: 37px;
    border-radius: 0.25rem;
}

.discount-table {
    width: 100%;
    border-collapse: collapse;

    th,
    td {
        padding: 0.25rem;
        border-bottom: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;

        &:last-of-type {
            border-right: none;
        }
    }

    tr {
        &:last-of-type {
            td {
                border-bottom: none;
            }
        }
    }
}
</style>

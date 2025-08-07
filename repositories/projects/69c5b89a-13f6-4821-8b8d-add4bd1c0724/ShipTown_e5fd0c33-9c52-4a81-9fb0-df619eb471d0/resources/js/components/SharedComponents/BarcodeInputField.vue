<template>
    <div>
        <div class="input-wrapper w-100">
            <input :id="getInputId"
                   :placeholder="placeholder"
                   :disabled="disabled"
                   type=text
                   class="form-control barcode-input"
                   autocomplete="off"
                   autocapitalize="off"
                   enterkeyhint="done"
                   ref="barcode"
                   dusk="barcode-input-field"
                   v-model.trim="barcode"
                   @keyup.enter="barcodeScanned(barcode)"
            />
            <div class="">
                <button v-if="showManualSearchButton" @click="findProduct" type="button"
                        class="btn button-search text-secondary ml-1 md:ml-2">
                    <font-awesome-icon icon="magnifying-glass" class="text-secondary"/>
                </button>
                <button v-if="showBarcodeScannerButton" @click="scanBarcode(barcodeScanned)" type="button"
                        class="btn button-barcode text-secondary ml-5 md:ml-2">
                    <img src="/barcode-generator?content=S&color=gray" alt="">
                </button>
            </div>
        </div>

        <barcode-scanner
            v-if="showModalBarcodeScanner"
            :showOnScreenScannerButton="showOnScreenScannerButton"
            @modalHidden="onCloseModal"
            @toggleButton="toggleShowOnScreenScannerButton"
        />

        <div style="position: fixed; left: 0; bottom: 0; height: 30px;" class="w-100 text-center" v-if="showOnScreenScannerButton">
            <div @click="scanBarcode(barcodeScanned)" class="btn btn-outline-primary rounded-circle bg-warning shadow"
                 style="opacity: 85%; border: solid 2px black; height: 60px; width: 60px; position: relative; top: -40px; font-size: 24pt; color: black;">
                <img src="/barcode-generator?content=S&color=dark gray" alt="" style="position: relative; top: -6px;">
            </div>
        </div>
    </div>
</template>

<script>
import url from "../../mixins/url";
import FiltersModal from "../Packlist/FiltersModal";
import api from "../../mixins/api";
import BarcodeScanner from "../../modals/BarcodeScannerModal.vue";
import helpers from "../../helpers";

export default {
    name: "BarcodeInputField",
    components: {BarcodeScanner},

    mixins: [helpers, url, FiltersModal, api],

    props: {
        input_id: null,
        url_param_name: null,
        placeholder: '',
        showManualSearchButton: {
            type: Boolean,
            default: false,
        },
        showKeyboardOnFocus: {
            type: Boolean,
            default: false,
        },
        showBarcodeScannerButton: {
            type: Boolean,
            default: true,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        autoFocusAfter: {
            type: Number,
            default: 100,
        },
        runCommand: {
            type: Boolean,
            default: true,
        },
    },

    computed: {
        getInputId() {
            if (this.input_id) {
                return this.input_id;
            }

            return `barcode-input-field-${Math.floor(Math.random() * 10000000)}`;
        },
    },

    data: function () {
        return {
            typedInText: '',
            currentLocation: '',
            barcode: '',
            command: ['', ''],

            showOnScreenScannerButton: false,
            showModalBarcodeScanner: false,
        }
    },

    watch: {
        '$route'(to, from) {
            this.$nextTick(() => {
                this.barcode = this.getUrlParameter(this.url_param_name, '');
                this.setFocusOnBarcodeInput();
            });
        },
    },

    mounted() {
        this.toggleShowOnScreenScannerButton(window.localStorage.getItem('showOnScreenScannerButton') === 'true');

        const isIos = () => !!window.navigator.userAgent.match(/iPad|iPhone/i);

        if (isIos()) {
            console.log('On iPhones and iPads, devices autofocus on input fields is disabled due to a bug in iOS. This works ok with external keyboards on iOS >16');
        }

        this.importValueFromUrlParam();

        if (this.autoFocusAfter > 0) {
            this.setFocusElementById(this.getInputId, this.showKeyboardOnFocus)
        }

        window.addEventListener('keydown', (e) => {
            if (e.target.nodeName !== 'BODY') {
                return;
            }

            if (e.ctrlKey || e.metaKey || e.altKey || e.shiftKey) {
                return;
            }

            if (e.key === 'Enter') {
                this.barcode = this.typedInText;
                this.barcodeScanned(this.typedInText);
                return;
            }

            this.typedInText += e.key;
        });
    },

    methods: {
        findProduct() {
            this.$modal.showFindProductModal(this.barcodeFoundManuallyCallback);
        },

        barcodeFoundManuallyCallback(product) {
            this.barcodeScanned(product['sku']);
        },

        scanBarcode(atBarcodeScannedCallback) {
            this.showModalBarcodeScanner = true;
            setTimeout(() => {
                this.$modal.showBarcodeScanner(atBarcodeScannedCallback);
            }, 300);
        },

        onScanSuccess(decodedText) {
            document.activeElement.value = decodedText;
            this.html5QrcodeScanner.stop();
            this.$bvModal.hide(this.getScannerModalID);
        },

        barcodeScanned(barcode) {
            if (barcode && barcode !== '') {
                this.apiPostActivity({
                    'log_name': 'search',
                    'description': barcode,
                })
                .catch((error) => {
                    this.displayApiCallError(error)
                });
            }

            if (this.tryToRunCommand(barcode)) {
                this.barcode = '';
                this.typedInText = '';
                return;
            }

            if (this.url_param_name) {
                this.setUrlParameter(this.url_param_name, barcode);
            }

            this.$emit('barcodeScanned', barcode);
            this.typedInText = '';
            this.barcode = barcode;

            this.setFocusOnBarcodeInput();
        },

        importValueFromUrlParam: function () {
            if (this.url_param_name) {
                this.barcode = this.getUrlParameter(this.url_param_name);
            }
        },

        showShelfLocationModal: function (command) {
            this.$modal.showShelfLocationCommandModal(command, this.closedShelfLocationModal);
            this.warningBeep();
        },

        closedShelfLocationModal: function () {
            this.importValueFromUrlParam();
            this.$emit('refreshRequest');
            this.setFocusOnBarcodeInput(false);
        },

        tryToRunCommand: function (textEntered) {
            if (textEntered === null || textEntered === '' || this.runCommand === false) {
                return false;
            }

            let text = textEntered
                .replace('https://myshiptown.com/?qr=', '');

            try {
                text = decodeURIComponent(text);
            } catch (e) {
                // noop
            }

            let command = text.split(':');

            if (command.length < 2) {
                return false;
            }

            this.command['name'] = command[0];
            this.command['value'] = command[1];

            switch (this.command['name'].toLowerCase()) {
                case 'shelf':
                    console.log(this.command)
                    this.showShelfLocationModal(this.command);
                    return true;
                case 'goto':
                    this.runGotoCommand();
                    return true;
            }

            return false;
        },

        runGotoCommand() {
            window.location.href = this.command['value'];
        },

        setFocusOnBarcodeInput(showKeyboard = false, autoSelectAll = true, delay = 100) {
            this.setFocusElementById(this.getInputId, showKeyboard, autoSelectAll, delay)
        },

        toggleShowOnScreenScannerButton(showing) {
            this.showOnScreenScannerButton = showing;
            window.localStorage.setItem('showOnScreenScannerButton', showing);
        },

        onCloseModal() {
            this.showModalBarcodeScanner = false;
            this.setFocusOnBarcodeInput();
        },
    }
}
</script>

<style scoped>
.barcode-input::selection {
    color: black;
    background: #cce3ff;
}

.input-wrapper {
    width: 100%;
    position: relative;
    display: inline-block;
}

.input-wrapper input {
    padding-right: 80px;
}

.input-wrapper button {
    position: absolute;
    border: none;
    background-color: transparent;
    cursor: pointer;
}

.button-barcode {
    right: 0;
    top: 0;
}

.button-search {
    right: 42px;
    top: 1px;
}
</style>

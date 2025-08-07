<template>
    <b-modal :id="modalId" scrollable no-fade hide-header
             @submit="updateShelfLocation"
             @shown="updateShelfLocationShown"
             @hidden="updateShelfLocationHidden">

        <div class="h5 text-center">{{ command }} : {{ shelf }}</div>
        <div v-if="shelfLocationModalContinuesScan" class="alert-success text-center mb-2 small">{{ $t('CONTINUES SCAN ENABLED') }}</div>

        <barcode-input-field
            :input_id="'set-shelf-location-command-modal-input'"
            :placeholder="$t('Scan product to update shelf location: ') + shelf"
            :run-command="false"
            ref="barcode"
            @barcodeScanned="updateShelfLocation"
        />

        <div class="mt-2 small">
            <div>
                <span class="text-primary font-weight-bold">{{ $t('Continuous Scanning') }}</span><span>- {{ $t('scan shelf again to enable') }}</span>
            </div>
            <div>
                <span class="text-danger font-weight-bold">{{ $t('Close') }}</span><span>- {{ $t('scan twice to close') }}</span>
            </div>
        </div>

        <template #modal-footer>
            <b-button class="mr-auto" variant="primary" :href="`/tools/printer?search=${shelf}`">{{ $t('Reprint') }}</b-button>
            <b-button variant="secondary" class="float-right" @click="closeModal">
                {{ $t('Cancel') }}
            </b-button>
            <b-button id="shelf_modal_ok_button" variant="primary" class="float-right" @click="updateShelfLocation">
                {{ $t('OK') }}
            </b-button>
        </template>
    </b-modal>
</template>


<script>
import BarcodeInputField from '../components/SharedComponents/BarcodeInputField.vue';
import helpers from "../helpers";
import api from "../mixins/api.vue";
import Modals from "../plugins/Modals";

export default {
    components: { BarcodeInputField },
    name: 'SetShelfLocationCommandModal',

    mixins: [api, helpers],

    beforeMount() {
        this.command = null;
        this.onClosedModal = null;

        Modals.EventBus.$on(`show::modal::${this.modalId}`, (data) => {
            console.log('set-shelf-location-command-modal', data)
            this.command = data.command['name'];
            this.shelf = data.command['value'];
            this.onClosedModal = data.onClosedModal;
            this.$bvModal.show(this.modalId);
            setTimeout(() => {
                this.setFocusElementById('set-shelf-location-command-modal-input')
            }, 500);
        })
    },

    data() {
        return {
            command: '',
            shelf: '',
            modalId: 'set-shelf-location-command-modal',

            shelfLocationModalCommandScanCount: 0,
            shelfLocationModalContinuesScan: false,
        }
    },

    methods: {
        closeModal() {
            this.$bvModal.hide(this.modalId);
        },

        updateShelfLocationShown: function () {
            this.shelfLocationModalContinuesScan = false;
            this.shelfLocationModalCommandScanCount = 0;
            this.setFocusElementById('set-shelf-location-command-modal-input')
        },

        updateShelfLocationHidden: function () {
            this.shelfLocationModalContinuesScan = false;
            this.shelfLocationModalCommandScanCount = 0;
            this.onClosedModal();
        },

        updateShelfLocation() {
            let textEntered = document.getElementById('set-shelf-location-command-modal-input').value.trim();
            if (textEntered === "") {
                return;
            }

            let lastCommand = this.command + ':' + this.shelf;

            if (textEntered === lastCommand) {
                setTimeout(() => {
                    document.getElementById('set-shelf-location-command-modal-input').value = '';
                }, 50)

                if (this.shelfLocationModalContinuesScan) {
                    this.closeModal();
                    return;
                }

                this.shelfLocationModalContinuesScan = true;
                return;
            }

            this.apiInventoryGet({
                'filter[sku_or_alias]': textEntered,
                'filter[warehouse_id]': this.currentUser()['warehouse_id'],
            })
                .then((response) => {
                    if (response.data['meta']['total'] !== 1) {
                        this.notifyError(this.$t('SKU {sku} not found ', {sku: event.target.value}));
                        return;
                    }

                    const inventory = response.data.data[0];
                    this.apiInventoryPost({
                        'id': inventory['id'],
                        'shelve_location': this.shelf,
                    })
                        .then(() => {
                            if (! this.shelfLocationModalContinuesScan) {
                                this.closeModal();
                            }
                            this.notifySuccess('Shelf updated');
                        })
                        .catch((error) => {
                            this.displayApiCallError(error)
                        });
                })
                .catch((error) => {
                    this.displayApiCallError(error)
                });

            if(this.shelfLocationModalContinuesScan) {
                this.setFocusElementById('set-shelf-location-command-modal-input')
            }
        },
    }
};
</script>

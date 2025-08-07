<template>
    <b-modal body-class="ml-0 mr-0 p-0" :id="modalId" size="sm" scrollable no-fade hide-header hide-footer>
        <div class="card card-default mb-0">
            <div class="card-header">
                <span>{{ $t('Change Shelf Label') }}</span>
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input id="shelf_label_input" v-model="shelfLabel" type="text" class="form-control text-center" @keydown.enter="save" />
                </div>

                <div class="row mt-4 d-flex justify-content-end">
                    <b-button variant="secondary" class="mr-2" @click="hide">{{ $t('Cancel') }}</b-button>
                    <b-button variant="primary" @click="save">{{ $t('Save') }}</b-button>
                </div>
            </div>
        </div>
    </b-modal>
</template>

<script>
import api from '../mixins/api.vue';
import Modals from '../plugins/Modals';

export default {
    mixins: [api],

    beforeMount() {
        Modals.EventBus.$on(`show::modal::${this.modalId}`, data => {
            this.inventory = data.inventory;
            this.shelfLabel = data.inventory['shelf_location'];
            this.$bvModal.show(this.modalId);
            this.setFocusElementById('shelf_label_input');
        });
    },

    data() {
        return {
            modalId: 'edit-shelf-label-modal',
            inventory: null,
            shelfLabel: ''
        }
    },

    watch: {
        shelfLabel(newVal) {
            const prefix = 'shelf:';
            if (typeof newVal === 'string' && newVal.toLowerCase().startsWith(prefix)) {
                this.shelfLabel = newVal.slice(prefix.length);
            }
        }
    },

    methods: {
        hide() {
            this.$bvModal.hide(this.modalId);
        },

        save() {
            if (!this.inventory) {
                return;
            }

            const originalLabel = this.inventory['shelf_location'];

            this.apiInventoryPost({
                id: this.inventory['id'],
                shelve_location: this.shelfLabel
            }).then(response => {
                if (response.data && response.data.data && response.data.data[0]) {
                    this.inventory['shelf_location'] = response.data.data[0]['shelf_location'];
                } else {
                    this.inventory['shelf_location'] = this.shelfLabel;
                }
                this.notifySuccess('Shelf updated');
                this.hide();
            }).catch(error => {
                this.inventory['shelf_location'] = originalLabel;
                this.displayApiCallError(error);
            });
        }
    }
}
</script>

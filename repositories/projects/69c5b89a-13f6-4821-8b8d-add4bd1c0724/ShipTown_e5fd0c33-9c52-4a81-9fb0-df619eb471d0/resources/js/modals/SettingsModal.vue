<template>
    <options-modal :id="modalId" :title="$t('Settings')" :show-stocktake-input="false" size="md" scrollable no-fade>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="list-group">
                        <a href="/settings/general" class="setting-list">
                            <div class="setting-icon">
                                <font-awesome-icon icon="cog" class="fa-sm"/>
                            </div>
                            <div class="setting-body">
                                <div class="setting-title">{{ $t('General') }}</div>
                                <div class="setting-desc">{{ $t('View and setting-desc application settings') }}</div>
                            </div>
                        </a>
                        <a href="/settings/users" class="setting-list">
                            <div class="setting-icon">
                                <font-awesome-icon icon="users" class="fa-sm"/>
                            </div>
                            <div class="setting-body">
                                <div class="setting-title">{{ $t('Users') }}</div>
                                <div class="setting-desc">{{ $t('Manage users') }}</div>
                            </div>
                        </a>
                        <a href="/settings/warehouses" class="setting-list">
                            <div class="setting-icon">
                                <font-awesome-icon icon="warehouse" class="fa-sm"/>
                            </div>
                            <div class="setting-body">
                                <div class="setting-title">{{ $t('Warehouses') }}</div>
                                <div class="setting-desc">{{ $t('Manage warehouses') }}</div>
                            </div>
                        </a>
                        <a href="/settings/automations?sort=priority,from_status_code,to_status_code" class="setting-list" dusk="order-automations-link">
                            <div class="setting-icon">
                                <font-awesome-icon icon="magic" class="fas-sm"/>
                            </div>
                            <div class="setting-body">
                                <div class="setting-title">{{ $t('Order Automations') }}</div>
                                <div class="setting-desc">{{ $t('Automate Order Workflows') }}</div>
                            </div>
                        </a>
                        <a href="/settings/mail-templates" class="setting-list" id="setting-mail-templates">
                            <div class="setting-icon">
                                <font-awesome-icon icon="envelope-open-text" class="fas-sm"/>
                            </div>
                            <div class="setting-body">
                                <div class="setting-title">{{ $t('Email Templates') }}</div>
                                <div class="setting-desc">{{ $t('Manage Email notification templates') }}</div>
                            </div>
                        </a>
                        <a href="/settings/order-statuses" class="setting-list">
                            <div class="setting-icon">
                                <font-awesome-icon icon="box-open" class="fa-sm"/>
                            </div>
                            <div class="setting-body">
                                <div class="setting-title">{{ $t('Order Statuses') }}</div>
                                <div class="setting-desc">{{ $t('Manage order statuses') }}</div>
                            </div>
                        </a>
                        <a href="/settings/navigation-menu" class="setting-list">
                            <div class="setting-icon">
                                <font-awesome-icon icon="list-ul" class="fas-sm"/>
                            </div>
                            <div class="setting-body">
                                <div class="setting-title">{{ $t('Navigation') }}</div>
                                <div class="setting-desc">{{ $t('Manage navigation menu items') }}</div>
                            </div>
                        </a>
                        <a href="/settings/modules" class="setting-list" dusk="goToModulesPage">
                            <div class="setting-icon">
                                <font-awesome-icon icon="puzzle-piece" class="fa-sm"/>
                            </div>
                            <div class="setting-body">
                                <div class="setting-title">{{ $t('Modules') }}</div>
                                <div class="setting-desc">{{ $t('Manage Modules') }}</div>
                            </div>
                        </a>
                        <a href="/settings/api" class="setting-list">
                            <div class="setting-icon">
                                <font-awesome-icon icon="key" class="fa-sm"/>
                            </div>
                            <div class="setting-body">
                                <div class="setting-title">{{ $t('API') }}</div>
                                <div class="setting-desc">{{ $t('View and update application API tokens and OAuth clients') }}</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </options-modal>
</template>

<script>
import Modals from "../plugins/Modals";
import OptionsModal from "../components/OptionsModal.vue";
export default {
    components: {OptionsModal},
    data() {
        return {
            modalId: 'settings-modal'
        };
    },
    mounted() {
        Modals.EventBus.$on('show::modal::' + this.modalId, () => {
            this.$bvModal.show(this.modalId);
        });
    }
}
</script>

<style scoped>
.setting-list{
    border-radius:1px;
    padding:0.5rem 1rem;
    background-color:#fff;
    border:1px solid rgba(0,0,0,0.125);
    width:100%;
    color:#495057;
    display:flex;
    align-items:flex-start;
    margin-bottom:5px;
}
.setting-list:hover,.setting-list:focus{
    color:#495057;
    text-decoration:none;
    background-color:#f8f9fa;
}
.setting-icon{
    padding:1rem;
    margin-right:1rem;
    background-color:#f8f9fa;
    border-radius:0.25rem;
}
.setting-icon:hover{
    background-color:unset;
}
.setting-title{
    color:#3490dc;
    font-weight:bolder;
    margin-bottom:2px;
}
.setting-desc{
    color:#6c757d;
    font-size:10pt;
}
</style>

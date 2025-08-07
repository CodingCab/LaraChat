<template>
    <div>
        <div class="row pl-2 p-0">
            <div class="col-12 text-left align-bottom pb-0 m-0 font-weight-bold text-uppercase small text-secondary">
                {{ $t('Settings > Modules > Stocktake Suggestions AI') }}
            </div>
        </div>
        <div class="card card-default mt-2">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    {{ $t('Manimum allowed last counted date') }}
                </div>
            </div>
            <div class="card-body m-0 ">
                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="">
                            {{ $t("Automatically request stock checks for products which haven't been counted since that date") }}
                        </div>
                        <div class="small text-secondary mt-2 mb-2">
                            {{ $t('Products that have been out of stock since this date will be excluded from check requests as when new products arrived, staff would have confirmed the stock level') }}
                        </div>
                    </div>
                    <div class="col-12 col-lg-3"></div>
                    <div class="col-12 col-lg-3 text-right m-sm-auto"><input class="form-control fa-pull-right" id="min_count_date" type="date" v-model="min_count_date" @change="save()"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import api from "../../../mixins/api.vue";
import moment from "moment/moment";

export default {
    mixins: [api],

    data: () => ({
        min_count_date: null,
        configuration: {
            min_count_date: null,
        },
    }),

    mounted() {
        this.load();
    },

    methods: {
        save() {
            this.configuration.min_count_date = this.min_count_date;

            this.$nextTick(() => {
                this.apiPostStocktakeSuggestionsConfiguration(this.configuration)
                    .then(response => {
                        console.log(response);
                    })
                    .catch(error => {
                        this.displayApiCallError(error);
                    });
            });
        },
        load() {
            this.apiGetStocktakeSuggestionsConfiguration('settings/stocktake-suggestions')
                .then(response => {
                    this.configuration = response.data.data;
                    this.min_count_date = moment(this.configuration.min_count_date).format('YYYY-MM-DD');
                    console.log(this.configuration);
                })
                .catch(error => {
                    this.displayApiCallError(error)
                });
        }
    },
}

</script>

<style lang="scss" scoped>

</style>

<template>
    <div :id="getElementId" class="swiper-container">
        <div class="swiper-wrapper">

            <div class="swiper-slide">
<!--            <div class="swiper-slide rounded bg-success text-right">-->
<!--                <div class="swipe-action-container swipe-action-container&#45;&#45;right">-->
<!--                    <div>SHIP THEM ALL</div>-->
<!--                </div>-->
            </div>

            <div class="swiper-slide">
                <div class="row">
                    <div class="col p-2 pl-3 ml-1 mr-1 rounded disabled">
                        <entry-card :entry="picklistItem"/>
                    </div>
                </div>
            </div>

            <div class="swiper-slide bg-warning">
                <div class="swipe-action-container swipe-action-container--left text-black-50 font-weight-bold">
                    <div>{{ $t('SHIP PARTIAL / UNDO') }}</div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    import Swiper from 'swiper';
    // import Swiper styles
    import 'swiper/swiper-bundle.css';
    import EntryCard from "./EntryCard";

    export default {
        name: 'PackedEntry',

        components: {EntryCard},

        props: {
            picklistItem: Object,
        },

        mounted() {
            const swipedRightIndex = 0;
            const swipedLeftIndex = 2;

            const self = this;
            const pickedItem = this.picklistItem;

            // Initialize Swiper
            const swiper = new Swiper('#' + this.getElementId, {
                initialSlide: 1,
                shortSwipes: false,
                longSwipes: true,
                longSwipesRatio: 0.30,
                longSwipesMs: 150,
                resistanceRatio: 0,
                speed: 150
            });

            // Event will be fired after transition
            swiper.on('transitionEnd', function() {

                if (this.activeIndex === swipedLeftIndex) {
                    self.$emit('swipeLeft', self.picklistItem);

                } else if (this.activeIndex === swipedRightIndex) {
                    self.$emit('swipeRight', self.picklistItem);
                }

                this.slideTo(1,0,false);
            });

        },
        computed: {
            getElementId() {
                return `picklist-item-${this.picklistItem.id}`;
            }
        },

    }
</script>

<style lang="scss" scoped>
    .col {
        background-color: #ffffff;
    }

    .header-row > div, .col {
        border: 1px solid #76777838;
    }

    .header-row > div {
        background-color: #76777838;
    }

    .disabled > div {
        opacity: 0.5;
    }

    .swiper-container {
        overflow: hidden;
    }

    .swiper-slide {
        height: auto;
    }
</style>

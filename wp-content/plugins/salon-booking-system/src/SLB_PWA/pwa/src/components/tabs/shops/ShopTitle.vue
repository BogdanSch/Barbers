<template>
    <h4 v-if="shop" class="shop-title">
        <span class="label">
            {{ $t('shopTitleLabel') }}:
        </span>
        <span class="value">
            {{ name }}
        </span>
    </h4>
</template>

<script>
    export default {
        name: 'ShopTitle',
        props: {
            shop: {
                default: function () {
                    return {};
                },
            },
        },
        computed: {
            name() {
                return this.shop.name
            },
        }
    }
</script>

<style scoped>
    .shop-title {
        text-align: left;
        font-size: 1.2rem;
        margin-bottom: 15px;
    }
    .label {
        font-weight: normal;
    }
    .value {
        font-weight: bold;
        color: #04409F;
    }
</style>

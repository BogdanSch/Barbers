<template>
    <b-row>
        <b-col sm="12" class="shop-wrapper">
            <div class="shop">
                <b-row>
                    <b-col sm="12" class="shop-info">
                        <div class="shop-name">
                            {{ name }}
                        </div>
                        <div class="shop-address">
                            {{ address }}
                        </div>
                        <div class="shop-email">
                            {{ email }}
                        </div>
                        <div class="shop-actions">
                            <div class="shop-phone">
                                {{ phone }}
                            </div>
                            <div class="shop-actions-wrapper">
                                <span class="details-link">
                                    <font-awesome-icon icon="fa-solid fa-chevron-right" @click="applyShop"/>
                                </span>
                            </div>
                        </div>
                    </b-col>
                </b-row>
            </div>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        name: 'ShopItem',
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
            address() {
                return this.shop.address
            },
            email() {
                return this.shop.email
            },
            phone() {
                return this.shop.phone
            },
        },
        methods: {
            applyShop() {
                this.$emit('applyShop', this.shop)
            },
        },
        emits: ['applyShop']
    }
</script>

<style scoped>
    .shop {
        padding: 10px 10px 10px 20px;
        text-align: left;
        margin-bottom: 1rem;
        background-color: #ECF1FA9B;
    }
    .shop-name {
        white-space: nowrap;
        overflow: hidden;
        color: #04409F;
        font-size: 22px;
        text-overflow: ellipsis;
    }
    .shop-actions {
        display: flex;
        justify-content: space-between;
    }
    .shop-actions-wrapper {
        display: flex;
        justify-content: flex-end;
        align-items: baseline;
        font-size: 20px;
    }
    .details-link {
        color: #04409F;
    }
    .shop-info {
        display: flex;
        color: #637491;
        font-size: 18px;
        flex-direction: column;
    }
    .details-link {
        cursor: pointer;
    }
</style>

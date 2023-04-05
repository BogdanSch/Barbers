<template>
    <b-row>
        <b-col sm="12">
            <div class="customer">
                <b-row>
                    <b-col sm="8" class="customer-info">
                        <div class="customer-first-last-name-wrapper">
                            <span class="customer-firstname">
                                {{ customerFirstname }}
                            </span>
                            <span class="customer-lastname">
                                {{ customerLastname }}
                            </span>
                        </div>
                        <div class="customer-email">
                            {{ customerEmail }}
                        </div>
                        <div class="customer-phone" v-if="customerPhone">
                            {{ customerPhone }}
                        </div>
                    </b-col>
                    <b-col sm="4" class="total-order-wrapper">
                        <span class="total-order-sum">
                            <font-awesome-icon icon="fa-solid fa-chart-simple" />
                            <span v-html="totalSum"></span>
                        </span>
                        <span class="total-order-count">
                            <font-awesome-icon icon="fa-solid fa-medal" />
                            {{ totalCount }}
                        </span>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col sm="12" class="total-info">
                        <div>
                            <div v-if="chooseCustomerAvailable" class="button-choose">
                                <font-awesome-icon icon="fa-solid fa-circle-plus" @click.prevent="choose" />
                            </div>
                        </div>
                        <div class="customer-phone-wrapper">
                            <span v-if="customerPhone">
                                <a :href="'tel:' + customerPhone" class="phone">
                                    <font-awesome-icon icon="fa-solid fa-phone" />
                                </a>
                                <a :href="'sms:' + customerPhone" class="sms">
                                    <font-awesome-icon icon="fa-solid fa-message" />
                                </a>
                                <a :href="'https://wa.me/' + customerPhone" class="whatsapp">
                                    <font-awesome-icon icon="fa-brands fa-whatsapp" />
                                </a>
                            </span>
                        </div>
                    </b-col>
                </b-row>
            </div>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        name: 'CustomerItem',
        props: {
            customer: {
                default: function () {
                    return {};
                },
            },
            chooseCustomerAvailable: {
                default: function () {
                    return false;
                },
            },
        },
        computed: {
            customerFirstname() {
                return this.customer.first_name
            },
            customerLastname() {
                return this.customer.last_name
            },
            customerEmail() {
                return this.customer.email
            },
            customerPhone() {
                return this.customer.phone ? this.customer.phone_country_code + this.customer.phone : ''
            },
            totalSum() {
                return this.$root.settings.currency_symbol + this.customer.total_amount_reservations;
            },
            totalCount() {
                return this.customer.bookings.length > 0 ? this.customer.bookings.length : '-';
            },
        },
        methods: {
            choose() {
                this.$emit('choose')
            }
        },
        emits: ['choose']
    }
</script>

<style scoped>
    .customer {
        padding: 10px;
        text-align: left;
        margin-bottom: 1rem;
        background-color: #ECF1FA9B;
        color: #637491;
    }
    .customer-firstname {
        margin-right: 5px;
    }
    .total-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .total-order-sum {
        margin-right: 15px;
    }

    .total-order-sum .fa-chart-simple {
        margin-right: 5px;
    }
    .fa-chart-simple,
    .fa-medal {
        color: #C7CED9;
        font-size: 24px;
    }
    .phone,
    .sms,
    .whatsapp {
        color: #04409F;
        font-size: 30px;
    }
    .button-choose {
        font-size: 24px;
        color: #04409F;
    }
    .customer-first-last-name-wrapper,
    .customer-email {
        margin-bottom: 5px;
    }
    .customer-first-last-name-wrapper {
        color: #04409F;
        font-size: 22px;
    }
    .total-order-sum,
    .total-order-count {
        color: #637491;
        font-size: 20px;

    }
    .phone,
    .sms {
        margin-right: 20px;
    }
    .customer-phone-wrapper {
        text-align: right;
    }
    .total-order-wrapper {
        text-align: right;
    }
    @media (max-width: 576px) {
        .customer-info {
            width: 50%;
        }
        .total-order-wrapper {
            width: 50%;
        }
    }
</style>

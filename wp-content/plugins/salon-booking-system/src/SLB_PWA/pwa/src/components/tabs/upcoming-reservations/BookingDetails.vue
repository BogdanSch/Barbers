<template>
    <div v-show="show">
        <h5>
            {{ $t('bookingDetailsTitle') }}
        </h5>
        <b-row>
            <b-col sm="12">
                <div class="booking-details-customer-info">
                    <b-row>
                        <b-col sm="10"></b-col>
                        <b-col sm="2" class="actions">
                            <font-awesome-icon icon="fa-solid fa-circle-xmark" @click="close"/>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="6">
                            <div class="date">
                                <span>{{ $t('dateTitle') }}</span><br/>
                                <font-awesome-icon icon="fa-solid fa-calendar-days" /> {{ date }}
                            </div>
                        </b-col>
                        <b-col sm="6">
                            <div class="time">
                                <span>{{ $t('timeTitle') }}</span><br/>
                                <font-awesome-icon icon="fa-regular fa-clock" /> {{ time }}
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <div class="customer-firstname">
                                {{ customerFirstname }}
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <div class="customer-lastname">
                                {{ customerLastname }}
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <div class="customer-email">
                                {{ customerEmail }}
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <div class="customer-phone">
                                {{ customerPhone }}
                                <span class="customer-phone-actions" v-if="customerPhone">
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
        <b-row>
            <b-col sm="12">
                <div class="booking-details-extra-info">
                    <div class="booking-details-extra-info-header">
                        <div class="booking-details-extra-info-header-title">
                            {{ $t('extraInfoLabel') }}
                        </div>
                        <div>
                            <span
                                class="booking-details-extra-info-header-btn"
                                :class="visibleExtraInfo ? null : 'collapsed'"
                                :aria-expanded="visibleExtraInfo ? 'true' : 'false'"
                                aria-controls="collapse-2"
                                @click="visibleExtraInfo = !visibleExtraInfo"
                            >
                                <font-awesome-icon icon="fa-solid fa-circle-chevron-down" v-if="!visibleExtraInfo" />
                                <font-awesome-icon icon="fa-solid fa-circle-chevron-up" v-else />
                            </span>
                        </div>
                    </div>
                    <b-collapse id="collapse-2" class="booking-details-extra-info-fields" v-model="visibleExtraInfo">
                        <b-row v-for="field in customFieldsList" :key="field.key" class="booking-details-extra-info-field-row">
                            <b-col sm="12">
                                {{ field.label }}:<br/>
                                <strong>{{ field.value }}</strong>
                            </b-col>
                        </b-row>
                    </b-collapse>
                </div>
            </b-col>
        </b-row>
        <b-row>
            <b-col sm="12">
                <div class="booking-details-total-info">
                    <b-row v-for="(service, index) in services" :key="index">
                        <b-col sm="6">
                            <div class="service">
                                <strong>{{ service.service_name }} [<span v-html="service.service_price + booking.currency"></span>]</strong>
                            </div>
                        </b-col>
                        <b-col sm="6">
                            <div class="attendant">
                                {{ service.assistant_name }}
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="6">
                            <div class="total">
                                <b-row>
                                    <b-col sm="6">
                                        <strong>{{ $t('totalTitle') }}</strong>
                                    </b-col>
                                    <b-col sm="6">
                                        <strong><span v-html="totalSum"></span></strong>
                                    </b-col>
                                </b-row>
                            </div>
                        </b-col>
                        <b-col sm="6">
                            <div class="transaction-id">
                                <b-row>
                                    <b-col sm="6">
                                        {{ $t('transactionIdTitle') }}
                                    </b-col>
                                    <b-col sm="6">
                                        {{ transactionId }}
                                    </b-col>
                                </b-row>
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="6">
                            <div class="discount">
                                <b-row>
                                    <b-col sm="6">
                                        {{ $t('discountTitle') }}
                                    </b-col>
                                    <b-col sm="6" v-html="discount"></b-col>
                                </b-row>
                            </div>
                        </b-col>
                        <b-col sm="6"></b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="6">
                            <div class="deposit">
                                <b-row>
                                    <b-col sm="6">
                                        {{ $t('depositTitle') }}
                                    </b-col>
                                    <b-col sm="6" v-html="deposit"></b-col>
                                </b-row>
                            </div>
                        </b-col>
                        <b-col sm="6"></b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="6">
                            <div class="due">
                                <b-row>
                                    <b-col sm="6">
                                        {{ $t('dueTitle') }}
                                    </b-col>
                                    <b-col sm="6" v-html="due"></b-col>
                                </b-row>
                            </div>
                        </b-col>
                        <b-col sm="6"></b-col>
                    </b-row>
                </div>
            </b-col>
        </b-row>
        <b-row>
            <b-col sm="12">
                <div class="booking-details-status-info">
                    <b-row>
                        <b-col sm="6" class="status">
                            {{ status }}
                        </b-col>
                        <b-col sm="6">
                            <b-button variant="primary" @click="edit">
                                <font-awesome-icon icon="fa-solid fa-pen-to-square" />
                                {{ $t('editButtonLabel') }}
                            </b-button>
                        </b-col>
                    </b-row>
                </div>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    export default {
        name: 'BookingDetails',
        props: {
            booking: {
                default: function () {
                    return {};
                },
            },
        },
        computed: {
            date() {
                return this.dateFormat(this.booking.date)
            },
            time() {
                return this.timeFormat(this.booking.time)
            },
            customerFirstname() {
                return this.booking.customer_first_name
            },
            customerLastname() {
                return this.booking.customer_last_name
            },
            customerEmail() {
                return this.booking.customer_email
            },
            customerPhone() {
                return this.booking.customer_phone ? this.booking.customer_phone_country_code + this.booking.customer_phone : ''
            },
            services() {
                return this.booking.services
            },
            totalSum() {
                return this.booking.amount + this.booking.currency
            },
            transactionId() {
                return this.booking.transaction_id
            },
            discount() {
                return this.booking.discounts_details.length > 0 ? this.booking.discounts_details.map(item => item.name + ' (' + item.amount_string + ')').join(', ') : '-'
            },
            deposit() {
                return +this.booking.deposit > 0 ? (this.booking.deposit + this.booking.currency) : '-'
            },
            due() {
                return (+this.booking.amount - +this.booking.deposit) + this.booking.currency
            },
            status() {
                return this.$root.statusesList[this.booking.status].label
            },
            customFieldsList() {
                return this.booking.custom_fields.filter(i => ['html', 'file'].indexOf(i.type) === -1)
            },
        },
        mounted() {
            this.toggleShow()
        },
        data: function () {
            return {
                show: true,
                visibleExtraInfo: false,
            }
        },
        methods: {
            close() {
                this.$emit('close');
            },
            edit() {
                this.$emit('edit');
            },
            toggleShow() {
                this.show = false
                setTimeout(() => {
                    this.show = true
                }, 0)
            },
        },
        emits: ['close', 'edit']
    }
</script>

<style scoped>
    .booking-details-customer-info,
    .booking-details-total-info,
    .booking-details-status-info,
    .booking-details-extra-info {
        border: solid 1px #ccc;
        padding: 20px;
        text-align: left;
        margin-bottom: 20px;
    }
    .actions {
        text-align: right;
    }
    .date,
    .time,
    .customer-firstname,
    .customer-lastname,
    .customer-email,
    .customer-phone,
    .service,
    .attendant,
    .total,
    .transaction-id,
    .discount,
    .deposit,
    .due,
    .booking-details-extra-info-field-row {
        border-bottom: solid 1px #ccc;
        margin-bottom: 20px;
        padding-bottom: 5px;
    }
    .booking-details-status-info .row {
        align-items: center;
    }
    .actions .fa-circle-xmark {
        cursor: pointer;
    }
    .phone,
    .sms,
    .whatsapp {
        color: #04409F;
        font-size: 20px;
    }
    .customer-phone {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
    }
    .phone,
    .sms {
        margin-right: 15px;
    }
    .booking-details-extra-info-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .booking-details-extra-info-header-btn {
        font-size: 22px;
        color: #0d6efd;
    }
    .booking-details-extra-info-fields {
        margin-top: 20px;
    }
    @media (max-width: 576px) {
        .status {
            margin-bottom: 10px;
        }
    }
</style>

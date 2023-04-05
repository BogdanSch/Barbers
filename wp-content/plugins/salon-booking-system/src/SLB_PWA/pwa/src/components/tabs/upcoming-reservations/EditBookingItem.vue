<template>
    <div v-show="show">
        <h5>
            {{ $t('editReservationTitle') }}
        </h5>
        <EditBooking
            :bookingID="booking.id"
            :date="booking.date"
            :time="booking.time"
            :customerID="customer ? customer.id : booking.customer_id"
            :customerFirstname="customer ? customer.first_name : booking.customer_first_name"
            :customerLastname="customer ? customer.last_name : booking.customer_last_name"
            :customerEmail="customer ? customer.email : booking.customer_email"
            :customerPhone="customer ? customer.phone : booking.customer_phone"
            :customerAddress="customer ? customer.address : booking.customer_address"
            :customerNotes="customer ? customer.note : booking.note"
            :services="booking.services"
            :discounts="booking.discounts"
            :status="booking.status"
            :isLoading="isLoading"
            :isSaved="isSaved"
            :isError="isError"
            :errorMessage="errorMessage"
            :customFields="booking.custom_fields"
            :shop="booking.shop"
            @close="close"
            @chooseCustomer="chooseCustomer"
            @save="save"
        />
    </div>
</template>

<script>

    import EditBooking from './EditBooking.vue'

    export default {
        name: 'EditBookingItem',
        props: {
            booking: {
                default: function () {
                    return {};
                },
            },
            customer: {
                default: function () {
                    return {};
                },
            },
        },
        components: {
            EditBooking,
        },
        mounted() {
            this.toggleShow()
        },
        data: function() {
            return {
                isLoading: false,
                isSaved: false,
                show: true,
            }
        },
        methods: {
            close() {
                this.$emit('close');
            },
            chooseCustomer() {
                this.$emit('chooseCustomer');
            },
            save(booking) {
                this.isLoading = true
                this.axios.put('bookings/' + this.booking.id, booking).then(() => {
                    this.isSaved = true
                    setTimeout(() => {
                        this.isSaved = false
                        this.close()
                    }, 3000)
                }, (e) => {
                    this.isError = true
                    this.errorMessage = e.response.data.message
                    setTimeout(() => {
                        this.isError = false
                        this.errorMessage = ''
                    }, 3000)
                }).finally(() => {
                    this.isLoading = false
                })
            },
            toggleShow() {
                this.show = false
                setTimeout(() => {
                    this.show = true
                }, 0)
            },
        },
        emits: ['close', 'chooseCustomer']
    }
</script>

<style scoped>

</style>

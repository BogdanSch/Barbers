<template>
    <div v-show="show">
        <h5>
            {{ $t('addReservationTitle') }}
        </h5>
        <EditBooking
            :date="date"
            :time="time"
            :customerID="customer ? customer.id : ''"
            :customerFirstname="customer ? customer.first_name : ''"
            :customerLastname="customer ? customer.last_name : ''"
            :customerEmail="customer ? customer.email : ''"
            :customerPhone="customer ? customer.phone : ''"
            :customerAddress="customer ? customer.address : ''"
            :customerNotes="customer ? customer.note : ''"
            status="sln-b-confirmed"
            :shop="shop"
            :isLoading="isLoading"
            :isSaved="isSaved"
            :isError="isError"
            :errorMessage="errorMessage"
            @close="close"
            @chooseCustomer="chooseCustomer"
            @save="save"
        />
    </div>
</template>

<script>

    import EditBooking from './../upcoming-reservations/EditBooking.vue'

    export default {
        name: 'AddBookingItem',
        props: {
            date: {
                default: function () {
                    return '';
                },
            },
            time: {
                default: function () {
                    return '';
                },
            },
            customer: {
                default: function () {
                    return {};
                },
            },
            shop: {
                default: function () {
                    return {};
                },
            },
        },
        mounted() {
            this.toggleShow()
        },
        components: {
            EditBooking,
        },
        data: function() {
            return {
                isLoading: false,
                isSaved: false,
                isError: false,
                errorMessage: '',
                show: true,
            }
        },
        methods: {
            close(booking) {
                this.$emit('close', booking);
            },
            chooseCustomer() {
                this.$emit('chooseCustomer');
            },
            save(booking) {
                this.isLoading = true
                this.axios.post('bookings', booking).then((response) => {
                    this.isSaved = true
                    setTimeout(() => {
                        this.isSaved = false
                    }, 3000)
                    this.axios.get('bookings/' + response.data.id).then((response) => {
                        this.close(response.data.items[0])
                    })
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

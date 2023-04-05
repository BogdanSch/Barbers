<template>
    <div>
        <b-spinner variant="primary" v-if="isLoading"></b-spinner>
        <CustomersAddressBook v-else-if="isChooseCustomer" @closeChooseCustomer="closeChooseCustomer" :chooseCustomerAvailable="true" @choose="choose" :shop="addItem ? shop : item.shop"/>
        <AddBookingItem v-else-if="addItem" @close="close" :date="date" :time="time" :customer="customer" @chooseCustomer="chooseCustomer" :shop="shop"/>
        <EditBookingItem v-else-if="editItem" :booking="item" :customer="customer" @close="closeEditItem" @chooseCustomer="chooseCustomer"/>
        <BookingDetails v-else-if="showItem" :booking="item" @close="closeShowItem" @edit="setEditItem"/>
        <ReservationsCalendar @showItem="setShowItem" v-else @add="add" :shop="shop"/>
    </div>
</template>

<script>

    import ReservationsCalendar from './reservations-calendar/ReservationsCalendar.vue'
    import AddBookingItem from './reservations-calendar/AddBookingItem.vue'
    import CustomersAddressBook from './customers-address-book/CustomersAddressBook.vue'
    import BookingDetails from './upcoming-reservations/BookingDetails.vue'
    import EditBookingItem from './upcoming-reservations/EditBookingItem.vue'

    export default {
        name: 'ReservationsCalendarTab',
        props: {
            shop: {
                default: function () {
                    return {};
                },
            }
        },
        components: {
            ReservationsCalendar,
            AddBookingItem,
            CustomersAddressBook,
            BookingDetails,
            EditBookingItem,
        },
        mounted() {
            let params = this.getQueryParams()
            if (typeof params['booking_id'] !== 'undefined') {
                this.isLoading = true;
                this.axios.get('bookings/' + params['booking_id']).then((response) => {
                    this.isLoading = false;
                    this.setShowItem(response.data.items[0])
                })
            }
        },
        data: function () {
            return {
                addItem: false,
                showItem: false,
                isChooseCustomer: false,
                item: null,
                editItem: false,
                customer: null,
                date: '',
                time: '',
                isLoading: false,
            };
        },
        methods: {
            add(date, time) {
                this.addItem = true;
                this.date = date
                this.time = time;
            },
            setShowItem(item) {
                this.showItem = true;
                this.item = item;
            },
            close(booking) {
                this.addItem = false;
                this.customer = null;
                if (booking) {
                    this.setShowItem(booking)
                }
            },
            chooseCustomer() {
                this.isChooseCustomer = true;
            },
            closeChooseCustomer() {
                this.isChooseCustomer = false;
            },
            closeShowItem() {
                this.showItem = false;
            },
            setEditItem() {
                this.editItem = true;
            },
            closeEditItem() {
                this.editItem = false;
                this.customer = null;
            },
            choose(customer) {
                this.customer = customer;
                this.closeChooseCustomer()
            },
        },
    }
</script>

<style>

</style>
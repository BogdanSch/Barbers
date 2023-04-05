<template>
    <div>
        <CustomersAddressBook v-if="isChooseCustomer" @closeChooseCustomer="closeChooseCustomer" :chooseCustomerAvailable="true" @choose="choose" :shop="item.shop"/>
        <EditBookingItem v-else-if="editItem" :booking="item" :customer="customer" @close="closeEditItem" @chooseCustomer="chooseCustomer"/>
        <BookingDetails v-else-if="showItem" :booking="item" @close="closeShowItem" @edit="setEditItem"/>
        <UpcomingReservations @showItem="setShowItem" v-show="!showItem" :shop="shop"/>
    </div>
</template>

<script>

    import UpcomingReservations from './upcoming-reservations/UpcomingReservations.vue'
    import BookingDetails from './upcoming-reservations/BookingDetails.vue'
    import EditBookingItem from './upcoming-reservations/EditBookingItem.vue'
    import CustomersAddressBook from './customers-address-book/CustomersAddressBook.vue'

    export default {
        name: 'UpcomingReservationsTab',
        props: {
            shop: {
                default: function () {
                    return {};
                },
            }
        },
        components: {
            UpcomingReservations,
            BookingDetails,
            EditBookingItem,
            CustomersAddressBook,
        },
        data: function () {
            return {
                showItem: false,
                editItem: false,
                item: null,
                isChooseCustomer: false,
                customer: null,
            }
        },
        methods: {
            setShowItem(item) {
                this.showItem = true;
                this.item = item;
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
            chooseCustomer() {
                this.isChooseCustomer = true;
            },
            closeChooseCustomer() {
                this.isChooseCustomer = false;
            },
            choose(customer) {
                this.customer = customer;
                this.closeChooseCustomer()
            },
        },
    }
</script>

<style scoped>

</style>
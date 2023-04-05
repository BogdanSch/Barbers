<template>
    <div>
        <CustomersAddressBook :shop="shop"/>
    </div>
</template>

<script>

    import CustomersAddressBook from './customers-address-book/CustomersAddressBook.vue'

    export default {
        name: 'CustomersAddressBookTab',
        props: {
            shop: {
                default: function () {
                    return {};
                },
            }
        },
        components: {
            CustomersAddressBook,
        },
    }
</script>

<style>

</style>
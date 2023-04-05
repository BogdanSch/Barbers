<template>
    <div>
        <h5 class="title">
            {{ $t('customersAddressBookTitle') }}
        </h5>
        <div class="search">
            <font-awesome-icon icon="fa-solid fa-magnifying-glass" class="search-icon" />
            <b-form-input v-model="search" class="search-input"></b-form-input>
            <font-awesome-icon icon="fa-solid fa-circle-xmark" class="clear" @click="search = ''" v-if="search"/>
        </div>
        <b-row>
            <b-col sm="12">
                <div class="filters">
                    <b-button
                        variant="outline-primary"
                        v-for="filter in filters"
                        :key="filter.value"
                        @click="searchFilter = filter.value"
                        :pressed="searchFilter === filter.value"
                    >
                        {{ filter.label }}
                    </b-button>
                </div>
            </b-col>
        </b-row>
        <div class="customers-list">
            <b-spinner variant="primary" v-if="isLoading"></b-spinner>
            <template v-else-if="customersList.length > 0">
                <CustomerItem
                    v-for="customer in customersList"
                    :key="customer.id"
                    :customer="customer"
                    :chooseCustomerAvailable="chooseCustomerAvailable"
                    @choose="choose(customer)"
                />
            </template>
            <template v-else>
                <span class="no-result">{{ $t('customersAddressBookNoResultLabel') }}</span>
            </template>
        </div>
        <b-button variant="primary" class="go-back" @click="closeChooseCustomer" v-if="chooseCustomerAvailable">
            {{ $t('goBackButtonLabel') }}
        </b-button>
    </div>
</template>

<script>

    import CustomerItem from './CustomerItem.vue';

    export default {
        name: 'CustomersAddressBook',
        props: {
            chooseCustomerAvailable: {
                default: function () {
                    return false;
                },
            },
            shop: {
                default: function () {
                    return {};
                },
            },
        },
        mounted() {
            this.load();
        },
        watch: {
            searchFilter(newVal) {
                newVal && this.load();
            },
            search(newVal) {
                if (newVal) {
                    this.searchFilter = ''
                    this.loadSearch()
                } else {
                    this.searchFilter = 'a|b'
                }
            },
            shop() {
                this.load()
            },
        },
        data: function () {
            return {
                filters: [
                    {label: 'a - b', value: 'a|b'},
                    {label: 'c - d', value: 'c|d'},
                    {label: 'e - f', value: 'e|f'},
                    {label: 'g - h', value: 'g|h'},
                    {label: 'i - j', value: 'i|j'},
                    {label: 'k - l', value: 'k|l'},
                    {label: 'm - n', value: 'm|n'},
                    {label: 'o - p', value: 'o|p'},
                    {label: 'q - r', value: 'q|r'},
                    {label: 's - t', value: 's|t'},
                    {label: 'u - v', value: 'u|v'},
                    {label: 'w - x', value: 'w|x'},
                    {label: 'y - z', value: 'y|z'},
                ],
                searchFilter: 'a|b',
                customersList: [],
                isLoading: false,
                search: '',
                timeout: null,
            }
        },
        methods: {
            closeChooseCustomer() {
                this.$emit('closeChooseCustomer')
            },
            choose(customer) {
                this.$emit('choose', customer)
            },
            load() {
                this.isLoading = true;
                this.customersList = [];
                this.axios
                    .get('customers', {params: {search: this.searchFilter, search_type: 'start_with', search_field: 'first_name', order_by: 'first_name_last_name', shop: this.shop ? this.shop.id : null}})
                    .then((response) => {
                        this.customersList = response.data.items
                    })
                    .finally(() => {
                        this.isLoading = false
                    })
            },
            loadSearch() {
                this.timeout && clearTimeout(this.timeout)
                this.timeout = setTimeout(() => {
                    this.isLoading = true;
                    this.customersList = [];
                    this.axios
                        .get('customers', {params: {search: this.search, order_by: 'first_name_last_name', shop: this.shop ? this.shop.id : null}})
                        .then((response) => {
                            this.customersList = response.data.items
                        })
                        .finally(() => {
                            this.isLoading = false
                        })
                }, 1000)
            },
        },
        components: {
            CustomerItem,
        },
        emits: ['closeChooseCustomer', 'choose']
    }
</script>

<style scoped>
    .filters,
    .customers-list,
    .go-back {
        margin-top: 1.5rem;
    }
    .search {
        position: relative;
    }
    .clear {
        position: absolute;
        top: 10px;
        z-index: 1000;
        right: 15px;
        cursor: pointer;
    }
    .go-back {
        text-transform: uppercase;
    }
    .title {
        text-align: left;
        font-weight: bold;
        color: #322D38;
        font-size: 22px;
    }
    .search-icon {
        position: absolute;
        z-index: 1000;
        top: 12px;
        left: 15px;
        color: #7F8CA2;
    }
    .search .search-input {
        padding-left: 40px;
        padding-right: 20px;
        border-radius: 30px;
        border-color: #7F8CA2;
    }
    .search {
        margin-top: 1.5rem;
    }
    .filters .btn {
        border-radius: 30px;
        padding: 4px 20px;
        background-color: #E1E6EF9B;
        color: #04409F;
        border-color: #7F8CA2;
        text-transform: uppercase;
        margin-right: 20px;
    }
    .filters .btn:hover,
    .filters .btn.active {
        color: #04409F;
        background-color: #7F8CA2;
        border-color: #7F8CA2;
    }
    .filters {
        white-space: nowrap;
        overflow: auto;
    }
    .filters::-webkit-scrollbar {
        display: none;
    }
</style>
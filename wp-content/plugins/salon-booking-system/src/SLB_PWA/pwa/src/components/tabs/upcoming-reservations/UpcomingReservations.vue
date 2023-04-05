<template>
    <div>
        <h5 class="title">
            {{ $t('upcomingReservationsTitle') }}
        </h5>
        <div class="search">
            <font-awesome-icon icon="fa-solid fa-magnifying-glass" class="search-icon" />
            <b-form-input v-model="search" class="search-input"></b-form-input>
            <font-awesome-icon icon="fa-solid fa-circle-xmark" class="clear" @click="search = ''" v-if="search"/>
        </div>
        <b-row>
            <b-col sm="12">
                <div class="hours">
                    <b-button
                        v-for="hour in hours"
                        :key="hour.hours"
                        @click="hourValue = hour.hours"
                        :pressed="hourValue === hour.hours"
                        variant="outline-primary"
                    >
                        {{ hour.label }}
                    </b-button>
                </div>
            </b-col>
        </b-row>
        <b-row>
            <b-col sm="12">
                <div class="attendants">
                    <b-button
                        v-for="attendant in attendants"
                        :key="attendant.id"
                        variant="outline-primary"
                        :pressed="filterAttendant === attendant.id"
                        @click="filterAttendant === attendant.id ? filterAttendant = '' : filterAttendant = attendant.id"
                    >
                        {{ attendant.name }}
                    </b-button>
                </div>
            </b-col>
        </b-row>
        <div class="bookings-list">
            <b-spinner variant="primary" v-if="isLoading"></b-spinner>
            <template v-else-if="filteredBookingsList.length > 0">
                <BookingItem
                    v-for="booking in filteredBookingsList"
                    :key="booking.id"
                    :booking="booking"
                    @deleteItem="deleteItem(booking.id)"
                    @showDetails="showDetails(booking)"
                />
            </template>
            <template v-else>
                <span class="no-result">{{ $t('upcomingReservationsNoResultLabel') }}</span>
            </template>
        </div>
    </div>
</template>

<script>

    import BookingItem from './BookingItem.vue'

    export default {
        name: 'UpcomingReservations',
        props: {
            shop: {
                default: function () {
                    return {};
                },
            }
        },
        data: function () {
            return {
                hours: [
                    {label: this.$t('label8Hours'), hours: 8},
                    {label: this.$t('label24Hours'), hours: 24},
                    {label: this.$t('label3Days'), hours: 72},
                    {label: this.$t('label1Week'), hours: 168},
                ],
                hourValue: 8,
                bookingsList: [],
                isLoading: false,
                filterAttendant: '',
                search: '',
                timeout: null,
            }
        },
        mounted() {
            this.load();
        },
        components: {
            BookingItem,
        },
        watch: {
            hourValue(newVal) {
                newVal && this.load();
            },
            search(newVal) {
                if (newVal) {
                    this.hourValue = ''
                    this.loadSearch()
                } else {
                    this.hourValue = 8
                }
            },
            shop() {
                this.load()
            },
        },
        computed: {
            attendants() {
                var attendants = {};
                attendants[0] = {
                    id: '',
                    name: this.$t('allTitle'),
                };
                this.bookingsList.forEach((booking) => {
                    booking.services.forEach((service) => {
                        if (service.assistant_id > 0) {
                            attendants[service.assistant_id] = {
                                id: service.assistant_id,
                                name: service.assistant_name,
                            }
                        }
                    })
                })
                return Object.values(attendants).length > 1 ? Object.values(attendants) : [];
            },
            filteredBookingsList() {
                return this.bookingsList.filter((booking) => {
                    var existsAttendant = false
                    booking.services.forEach((service) => {
                        if (this.filterAttendant === service.assistant_id) {
                            existsAttendant = true
                        }
                    })
                    return this.filterAttendant === '' || existsAttendant
                });
            },
        },
        methods: {
            deleteItem(id) {
                this.axios
                    .delete('bookings/' + id)
                    .then(() => {
                        this.bookingsList = this.bookingsList.filter(item => item.id !== id)
                    })
            },
            showDetails(booking) {
                this.$emit('showItem', booking)
            },
            load() {
                this.isLoading = true;
                this.bookingsList = [];
                this.axios
                    .get('bookings/upcoming', {params: {hours: this.hourValue, shop: this.shop ? this.shop.id : null}})
                    .then((response) => {
                        this.bookingsList = response.data.items
                    })
                    .finally(() => {
                        this.isLoading = false
                    })
            },
            loadSearch() {
                this.timeout && clearTimeout(this.timeout)
                this.timeout = setTimeout(() => {
                    this.isLoading = true;
                    this.bookingsList = [];
                    this.axios
                        .get('bookings', {params: {
                            search: this.search,
                            per_page: -1,
                            order_by: 'date_time',
                            order: 'asc',
                            start_date: this.moment().format('YYYY-MM-DD'),
                            shop: this.shop ? this.shop.id : null,
                        }})
                        .then((response) => {
                            this.bookingsList = response.data.items
                        })
                        .finally(() => {
                            this.isLoading = false
                        })
                }, 1000)
            },
        },
        emits: ['showItem']
    }
</script>

<style scoped>
    .search,
    .bookings-list,
    .hours,
    .attendants {
        margin-top: 1.5rem;
    }
    .attendants .btn {
        margin-right: 20px;
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
    .attendants .btn {
        border-radius: 30px;
        padding: 4px 20px;
        background-color: #E1E6EF9B;
        color: #04409F;
        border-color: #7F8CA2;
    }
    .attendants .btn:hover,
    .attendants .btn.active {
        color: #04409F;
        background-color: #7F8CA2;
        border-color: #7F8CA2;
    }
    .attendants {
        white-space: nowrap;
        overflow: auto;
    }
    .attendants::-webkit-scrollbar {
        display: none;
    }
    .hours .btn {
        color: #C7CED9;
        border-top: 0;
        border-left: 0;
        border-right: 0;
        border-bottom-color: #C7CED9;
        border-radius: 0;
        border-bottom-width: 2px;
        background-color: #fff;
    }
    .hours .btn:hover,
    .hours .btn.active {
        background-color: #fff;
        color: #04409F;
        border-bottom-color: #04409F;
    }
</style>
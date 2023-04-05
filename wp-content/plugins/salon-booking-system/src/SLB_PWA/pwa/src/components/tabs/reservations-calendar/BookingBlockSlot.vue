<template>
    <b-row>
        <b-col sm="12">
            <div class="block-slot">
                <b-spinner variant="primary" v-if="isLoading"></b-spinner>
               <font-awesome-icon icon="fa-solid fa-unlock" v-else-if="!isLock" @click="lock"/>
               <font-awesome-icon icon="fa-solid fa-lock" v-else @click="unlock"/>
            </div>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        name: 'BookingBlockSlot',
        props: {
            isLock: {
                default: function () {
                    return false;
                },
            },
            start: {
                default: function () {
                    return '08:00';
                },
            },
            end: {
                default: function () {
                    return '08:30';
                },
            },
            date: {
                default: function () {
                    return null;
                },
            },
            shop: {
                default: function () {
                    return {};
                },
            },
        },
        data: function () {
            return {
                isLoading: false,
            }
        },
        computed: {
            holidayRule() {
                return {
                    from_date: this.moment(this.date).format('YYYY-MM-DD'),
                    to_date: this.moment(this.date).format('YYYY-MM-DD'),
                    from_time: this.moment(this.start, 'HH:mm').format('HH:mm'),
                    to_time: this.moment(this.end, 'HH:mm').format('HH:mm'),
                    daily: true,
                    shop: this.shop ? this.shop.id : 0,
                }
            },
        },
        methods: {
            lock() {
                this.isLoading = true;
                this.axios.post('holiday-rules', this.holidayRule).then((response) => {
                    this.$emit('lock', response.data.items)
                }).finally(() => {
                    this.isLoading = false
                })
            },
            unlock() {
                this.isLoading = true;
                this.axios.delete('holiday-rules', {data: this.holidayRule}).then((response) => {
                    this.$emit('unlock', response.data.items)
                }).finally(() => {
                    this.isLoading = false
                })
            },
        },
        emits: ['lock', 'unlock']
    }
</script>

<style scoped>
    .block-slot .fa-unlock,
    .block-slot .fa-lock {
        font-size: 30px;
        cursor: pointer;
    }
    .block-slot .fa-unlock {
        color: #04409F;
    }
    .block-slot .fa-lock {
        color: #C7CED9;
    }
</style>

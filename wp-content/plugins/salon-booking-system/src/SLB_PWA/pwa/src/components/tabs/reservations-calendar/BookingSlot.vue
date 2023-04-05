<template>
    <b-row>
        <b-col sm="12">
            <div class="timeslot">
                <span class="time">
                    {{ timeslotValue }}
                </span>
                <span class="line"></span>
                <span class="action">
                    <font-awesome-icon icon="fa-solid fa-circle-plus" :class="{available: isAvailable}" @click="add" />
                </span>
            </div>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        name: 'BookingSlot',
        props: {
            timeslot: {
                default: function () {
                    return '08:00';
                },
            },
            isAvailable: {
                default: function () {
                    return true;
                },
            },
        },
        computed: {
            timeslotValue() {
                return this.moment(this.timeslot, 'HH:mm').format('HH.mm')
            },
        },
        methods: {
            add() {
                this.$emit('add')
            },
        },
        emits: ['add']
    }
</script>

<style scoped>
    .timeslot {
        display: flex;
        margin-bottom: 10px;
        align-items: center;
        color: #7F8CA2;
    }
    .line {
        flex-grow: 1;
        border-bottom: dashed 1px #707070;
        height: 1px;
    }
    .time {
        margin-right: 15px;
    }
    .action {
        margin-left: 15px;
        color: #C7CED9;
        font-size: 30px;
    }
    .action .available {
        color: #04409F;
    }
</style>

<template>
    <div>
        <TabsList @applyShop="applyShop"/>
        <PWAPrompt/>
    </div>
</template>

<script>

import TabsList from './components/TabsList.vue'
import PWAPrompt from './components/PWAPrompt.vue'

export default {
    name: 'App',
    mounted() {
        this.loadSettings()
    },
    data: function () {
        return {
            settings : {},
            statusesList: {
                'sln-b-pendingpayment': {label: this.$t('pendingPaymentStatusLabel'), color: '#ffc107'},
                'sln-b-pending': {label: this.$t('pendingStatusLabel'), color: '#ffc107'},
                'sln-b-paid': {label: this.$t('paidStatusLabel'), color: '#28a745'},
                'sln-b-paylater': {label: this.$t('payLaterStatusLabel'), color: '#17a2b8'},
                'sln-b-error': {label: this.$t('errorStatusLabel'), color: '#dc3545'},
                'sln-b-canceled': {label: this.$t('canceledStatusLabel'), color: '#dc3545'},
                'sln-b-confirmed': {label: this.$t('confirmedStatusLabel'), color: '#28a745'},
            },
            shop: null,
        }
    },
    watch: {
        shop() {
            this.loadSettings()
        },
    },
    methods: {
        loadSettings() {
            this.axios.get('app/settings', {params: {shop: this.shop ? this.shop.id : null}}).then((response) => {
                this.settings = response.data.settings
            })
        },
        applyShop(shop) {
            this.shop = shop
        },
    },
    components: {
        TabsList,
        PWAPrompt,
    },
    beforeCreate() {
        if (this.$OneSignal) {
            this.$OneSignal.showSlidedownPrompt()
            this.$OneSignal.on('subscriptionChange', (isSubscribed) => {
                if (isSubscribed) {
                    this.$OneSignal.getUserId((userId) => {
                        if (userId) {
                            this.axios.put('users', {onesignal_player_id: userId})
                        }
                    });
                }
            });
        }
    },
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 50px;
}
.service-select .vue-dropdown .vue-dropdown-item.highlighted {
  background-color:#0d6efd;
}
.service-select .vue-dropdown .vue-dropdown-item.highlighted span,
.service-select .vue-dropdown .vue-dropdown-item.highlighted .option-item {
  color:#fff;
}
.service-select .vue-dropdown{
  background-color:#edeff2;
  padding: 0px 10px;
}
.service-select .vue-input {
  width:100%;
  font-size: 1rem;
}
</style>

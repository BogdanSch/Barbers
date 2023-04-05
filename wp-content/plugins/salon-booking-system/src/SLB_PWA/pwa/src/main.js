import { createApp } from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import store from './store'

import BootstrapVue3 from 'bootstrap-vue-3'

// Optional, since every component import their Bootstrap functionality
// the following line is not necessary
// import 'bootstrap'

import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue-3/dist/bootstrap-vue-3.css'

/* import the fontawesome core */
import { library } from '@fortawesome/fontawesome-svg-core'

/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

/* import specific icons */
import { faList, faCalendarDays, faMagnifyingGlass, faChevronRight, faTrash, faCircleXmark,
faPenToSquare, faCheck, faUsers, faPlus, faCircleChevronDown, faCircleChevronUp, faChartSimple, faMedal, faUnlock, faLock,
faPhone, faMessage, faCirclePlus, faStore } from '@fortawesome/free-solid-svg-icons'

import { faAddressBook, faClock } from '@fortawesome/free-regular-svg-icons'

import { faWhatsapp } from '@fortawesome/free-brands-svg-icons'

/* add icons to the library */
library.add(faList, faCalendarDays, faAddressBook, faMagnifyingGlass, faClock, faChevronRight, faTrash, faCircleXmark,
faPenToSquare, faCheck, faUsers, faPlus, faCircleChevronDown, faCircleChevronUp, faChartSimple, faMedal, faUnlock, faLock,
faPhone, faMessage, faCirclePlus, faWhatsapp, faStore)

import Datepicker from '@vuepic/vue-datepicker';

import '@vuepic/vue-datepicker/dist/main.css';
import mixin from './mixin'

import OneSignalVuePlugin from '@onesignal/onesignal-vue3'

import VueNextSelect from 'vue-next-select';
import 'vue-next-select/dist/index.min.css'

var app = createApp(App)
            .use(store)
            .use(BootstrapVue3)
            .component('font-awesome-icon', FontAwesomeIcon)
            .component('Datepicker', Datepicker)
            .component('vue-select', VueNextSelect)
            .mixin(mixin)

var oneSignalAppId = window.slnPWA.onesignal_app_id

if (oneSignalAppId) {
    app.use(OneSignalVuePlugin, {
        appId: oneSignalAppId,
        serviceWorkerParam: { scope: "/{SLN_PWA_DIST_PATH}/" },
        serviceWorkerPath: "{SLN_PWA_DIST_PATH}/OneSignalSDKWorker.js"
    })
}

import i18n from './i18n'

app.use(i18n)

app.mount('#app')
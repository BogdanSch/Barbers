import axios from 'axios'
import moment from 'moment'

export default {
    computed: {
        axios() {
            return axios.create({
                baseURL: window.slnPWA.api,
                headers: {'Access-Token': window.slnPWA.token},
            });
        },
        moment() {
            return moment
        },
        locale() {
            return window.slnPWA.locale
        }
    },
    methods: {
        dateFormat(date) {

            var format = this.$root.settings.date_format ? this.$root.settings.date_format.js_format : null;

            if (!format) {
                return date
            }

            var momentJsFormat = format
                                    .replace('dd', 'DD')
                                    .replace('M', 'MMM')
                                    .replace('mm', 'MM')
                                    .replace('yyyy', 'YYYY')

            return moment(date).format(momentJsFormat)
        },
        timeFormat(time) {
            return moment(time, 'HH:mm').format(this.getTimeFormat())
        },
        getTimeFormat() {

            var format = this.$root.settings.time_format ? this.$root.settings.time_format.js_format : null;

            if (!format) {
                return
            }

            var momentJsFormat = format.indexOf('p') > -1 ?
                                    format
                                        .replace('H', 'hh')
                                        .replace('p', 'a')
                                        .replace('ii', 'mm')
                                    :
                                    format
                                        .replace('hh', 'HH')
                                        .replace('ii', 'mm')

            return momentJsFormat
        },
        getQueryParams() {
            let query = window.location.search
            query = query.replace('?', '')
            let paramsList = query.split('&').map(i => ({key: i.split('=')[0], value: i.split('=')[1]}))
            let params = {};
            paramsList.forEach(i => {
                params[i.key] = i.value
            })
            return params;
        }
    },
}
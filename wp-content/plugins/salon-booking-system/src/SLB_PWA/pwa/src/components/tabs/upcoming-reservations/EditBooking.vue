<template>
    <div @click="showTimeslots = false">
        <b-row>
            <b-col sm="12">
                <div class="booking-details-customer-info">
                    <b-row>
                        <b-col sm="10"></b-col>
                        <b-col sm="2" class="actions">
                            <font-awesome-icon icon="fa-solid fa-circle-xmark" @click="close"/>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="6">
                            <div class="date">
                                <span>{{ $t('dateTitle') }}</span>
                                <b-input-group>
                                    <template #prepend>
                                        <b-input-group-text>
                                            <font-awesome-icon icon="fa-solid fa-calendar-days" />
                                        </b-input-group-text>
                                     </template>
                                    <Datepicker
                                        format="yyyy-MM-dd"
                                        v-model="elDate"
                                        :auto-apply="true"
                                        :text-input="true"
                                        :hide-input-icon="true"
                                        :clearable="false"
                                        :class="{required: requiredFields.indexOf('date') > -1}"
                                    ></Datepicker>
                                </b-input-group>
                            </div>
                        </b-col>
                        <b-col sm="6">
                            <div class="time">
                                <span>{{ $t('timeTitle') }}</span>
                                <b-input-group>
                                    <template #prepend>
                                        <b-input-group-text>
                                            <font-awesome-icon icon="fa-regular fa-clock" />
                                        </b-input-group-text>
                                    </template>
                                    <b-form-input v-model="elTime" @click.stop="showTimeslots = !showTimeslots" class="timeslot-input" :class="{required: requiredFields.indexOf('time') > -1}" />
                                    <div class="timeslots" :class="{hide: !this.showTimeslots}" @click.stop>
                                        <span v-for="timeslot in timeslots" :key="timeslot" class="timeslot" :class="{free: freeTimeslots.indexOf(this.moment(timeslot, this.getTimeFormat()).format('HH:mm')) > -1}" @click="setTime(timeslot)">
                                            {{ timeslot }}
                                        </span>
                                    </div>
                                </b-input-group>
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <div class="select-existing-client">
                                <b-button variant="primary" @click="chooseCustomer">
                                    <font-awesome-icon icon="fa-solid fa-users" />
                                    {{ $t('selectExistingClientButtonLabel') }}
                                </b-button>
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <div class="customer-firstname">
                                <b-form-input :placeholder="$t('customerFirstnamePlaceholder')" v-model="elCustomerFirstname" :class="{required: requiredFields.indexOf('customer_first_name') > -1}" />
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <div class="customer-lastname">
                                <b-form-input :placeholder="$t('customerLastnamePlaceholder')" v-model="elCustomerLastname"/>
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <div class="customer-email">
                                <b-form-input :placeholder="$t('customerEmailPlaceholder')" v-model="elCustomerEmail"/>
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <div class="customer-address">
                                <b-form-input :placeholder="$t('customerAddressPlaceholder')" v-model="elCustomerAddress"/>
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <div class="customer-phone">
                                <b-form-input :placeholder="$t('customerPhonePlaceholder')" v-model="elCustomerPhone"/>
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <div class="customer-notes">
                                <b-form-textarea
                                    v-model="elCustomerNotes"
                                    :placeholder="$t('customerNotesPlaceholder')"
                                    rows="3"
                                    max-rows="6"
                                ></b-form-textarea>
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                      <div class="save-as-new-customer">
                        <b-form-checkbox v-model="saveAsNewCustomer" switch
                        >{{ $t('saveAsNewCustomerLabel') }}</b-form-checkbox>
                      </div>
                    </b-row>
                </div>
            </b-col>
        </b-row>
        <b-row>
            <b-col sm="12">
                <div class="booking-details-extra-info">
                    <div class="booking-details-extra-info-header">
                        <div class="booking-details-extra-info-header-title">
                            {{ $t('extraInfoLabel') }}
                        </div>
                        <div>
                            <span
                                class="booking-details-extra-info-header-btn"
                                :class="visibleExtraInfo ? null : 'collapsed'"
                                :aria-expanded="visibleExtraInfo ? 'true' : 'false'"
                                aria-controls="collapse-2"
                                @click="visibleExtraInfo = !visibleExtraInfo"
                            >
                                <font-awesome-icon icon="fa-solid fa-circle-chevron-down" v-if="!visibleExtraInfo" />
                                <font-awesome-icon icon="fa-solid fa-circle-chevron-up" v-else />
                            </span>
                        </div>
                    </div>
                    <b-collapse id="collapse-2" class="mt-2" v-model="visibleExtraInfo">
                        <template v-for="field in customFieldsList" :key="field.key">
                            <CustomField :field="field" :value="getCustomFieldValue(field.key, field.default_value)" @update="updateCustomField"/>
                        </template>
                    </b-collapse>
                </div>
            </b-col>
        </b-row>
        <b-row>
            <b-col sm="12">
                <div class="booking-details-total-info">
                    <template v-if="!isLoadingServicesAssistants">
                    <b-row v-for="(service, index) in elServices" :key="index" class="service-row">
                        <b-col sm="5">
                            <div class="service">
                              <vue-select ref="select-service" class="service-select" close-on-select v-model="service.service_id" :options="getServicesListBySearch(servicesList, serviceSearch[index])" label-by="[serviceName, price, duration, category]" value-by="value" :class="{required: requiredFields.indexOf('services_service_' + index) > -1}">
                                <template #label="{ selected }">
                                  <template v-if="selected">
                                    <div class="option-item option-item-selected"><div class="name">
                                      <span>{{ selected.category }}</span>
                                      <span v-if="selected.category"> | </span>
                                      <span class="service-name">{{ selected.serviceName }}</span>
                                    </div>
                                    <div class="info">
                                      <div class="price">
                                        <span>{{ selected.price }}</span>
                                        <span v-html="selected.currency"></span>
                                        <span> | </span>
                                        <span>{{ selected.duration }}</span>
                                      </div>
                                    </div></div>
                                  </template>
                                  <template v-else>{{ $t('selectServicesPlaceholder') }}</template>
                                </template>
                                <template #dropdown-item="{ option }">
                                    <div class="option-item">
                                        <div class="availability-wrapper">
                                            <div class="availability" :class="{available: option.available}"></div>
                                            <div class="name">
                                                <span>{{ option.category }}</span>
                                                <span v-if="option.category"> | </span>
                                                <span class="service-name">{{ option.serviceName }}</span>
                                            </div>
                                        </div>
                                        <div class="info">
                                            <div class="price">
                                                <span>{{ option.price }}</span>
                                                <span v-html="option.currency"></span>
                                                <span> | </span>
                                                <span>{{ option.duration }}</span>
                                          </div>
                                        </div>
                                    </div>
                                </template>
                              </vue-select>
                                <li class="vue-select-search">
                                    <font-awesome-icon icon="fa-solid fa-magnifying-glass" class="vue-select-search-icon" />
                                    <b-form-input v-model="serviceSearch[index]" class="vue-select-search-input" :placeholder="$t('selectServicesSearchPlaceholder')" @mousedown.stop></b-form-input>
                                </li>
                            </div>
                        </b-col>
                        <b-col sm="5" v-if="isShowAttendant(service)">
                            <div class="attendant">
                              <vue-select ref="select-assistant" class="service-select" close-on-select v-model="service.assistant_id"  :options="getAttendantsListBySearch(attendantsList, assistantSearch[index])" label-by="text" value-by="value" :class="{required: requiredFields.indexOf('services_assistant_' + index) > -1}" @focus="loadAvailabilityAttendants(service.service_id)">
                                    <template #label="{ selected }">
                                        <template v-if="selected">
                                            <div class="option-item option-item-selected">
                                                <div class="name">
                                                    <span>{{ selected.text }}</span>
                                                </div>
                                            </div>
                                    </template>
                                    <template v-else>{{ $t('selectAttendantsPlaceholder') }}</template>
                                </template>
                                <template #dropdown-item="{ option }">
                                    <div class="option-item">
                                        <div class="availability-wrapper">
                                            <div class="availability" :class="{available: option.available}"></div>
                                            <div class="name">
                                                {{ option.text }}
                                            </div>
                                        </div>
                                    </div>
                                </template>
                              </vue-select>
                                <li class="vue-select-search">
                                    <font-awesome-icon icon="fa-solid fa-magnifying-glass" class="vue-select-search-icon" />
                                    <b-form-input v-model="assistantSearch[index]" class="vue-select-search-input" :placeholder="$t('selectAssistantsSearchPlaceholder')" @mousedown.stop></b-form-input>
                                </li>
                            </div>
                        </b-col>
                        <b-col sm="2" class="service-row-delete">
                            <font-awesome-icon icon="fa-solid fa-circle-xmark" @click="deleteService(index)"/>
                        </b-col>
                    </b-row>
                    </template>
                    <b-row>
                        <b-col sm="6" class="add-service-wrapper">
                            <div class="add-service">
                                <b-button variant="primary" @click="addService" :disabled="isLoadingServicesAssistants">
                                    <font-awesome-icon icon="fa-solid fa-plus" />
                                    {{ $t('addServiceButtonLabel') }}
                                </b-button>
                                <b-spinner variant="primary" class="services-assistants-loader" v-if="isLoadingServicesAssistants"></b-spinner>
                            </div>
                            <div class="add-service-required">
                                <b-alert :show="requiredFields.indexOf('services') > -1" fade variant="danger">{{ $t('addServiceMessage') }}</b-alert>
                            </div>
                        </b-col>
                    </b-row>
                </div>
            </b-col>
        </b-row>
        <b-row>
            <b-col sm="12">
                <div class="booking-discount-info">
                    <b-button
                    :class="visibleDiscountInfo ? null : 'collapsed'"
                    :aria-expanded="visibleDiscountInfo ? 'true' : 'false'"
                    aria-controls="collapse-1"
                    @click="visibleDiscountInfo = !visibleDiscountInfo"
                    variant="primary" >
                        {{ $t('addAndManageDiscountButtonLabel') }}
                        <font-awesome-icon icon="fa-solid fa-circle-chevron-down" v-if="!visibleDiscountInfo" />
                        <font-awesome-icon icon="fa-solid fa-circle-chevron-up" v-else />
                    </b-button>
                    <b-collapse id="collapse-1" class="mt-2" v-model="visibleDiscountInfo">
                        <b-row v-for="(discount, index) in elDiscounts" :key="discount" class="discount-row">
                            <b-col sm="6">
                                <div class="discount">
                                    <label>{{ $t('selectDiscountLabel') }}</label>
                                    <b-form-select v-model="elDiscounts[index]" :options="discountsList"></b-form-select>
                                </div>
                            </b-col>
                            <b-col sm="6" class="discount-row-delete">
                                <font-awesome-icon icon="fa-solid fa-circle-xmark" @click="deleteDiscount(index)"/>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col sm="6">
                                <b-button variant="primary" @click="addDiscount">
                                    <font-awesome-icon icon="fa-solid fa-plus" />
                                    {{ $t('addDiscountButtonLabel') }}
                                </b-button>
                            </b-col>
                        </b-row>
                    </b-collapse>
                </div>
            </b-col>
        </b-row>
        <b-row>
            <b-col sm="12">
                <div class="booking-details-status-info">
                    <b-row>
                        <b-col sm="6" class="status">
                            <b-form-select v-model="elStatus" :options="statusesList"></b-form-select>
                        </b-col>
                        <b-col sm="6">
                            <b-row>
                            <b-col sm="6" class="save-button-wrapper">
                                <b-button variant="primary" @click="save">
                                    <font-awesome-icon icon="fa-solid fa-check" />
                                    {{ $t('saveButtonLabel') }}
                                </b-button>
                            </b-col>
                            <b-col sm="6" class="save-button-result-wrapper">
                                <b-spinner variant="primary" v-if="isLoading"></b-spinner>
                                <b-alert :show="isSaved" fade variant="success">{{ $t('savedLabel') }}</b-alert>
                                <b-alert :show="isError" fade variant="danger">{{ errorMessage }}</b-alert>
                                <b-alert :show="!isValid" fade variant="danger">{{ $t('validationMessage') }}</b-alert>
                            </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                </div>
            </b-col>
        </b-row>
    </div>
</template>

<script>

    import CustomField from './CustomField.vue'

    export default {
        name: 'EditBooking',
        props: {
            bookingID: {
                default: function () {
                    return '';
                },
            },
            date: {
                default: function () {
                    return '';
                },
            },
            time: {
                default: function () {
                    return '';
                },
            },
            customerID: {
                default: function () {
                    return '';
                },
            },
            customerFirstname: {
                default: function () {
                    return '';
                },
            },
            customerLastname: {
                default: function () {
                    return '';
                },
            },
            customerEmail: {
                default: function () {
                    return '';
                },
            },
            customerAddress: {
                default: function () {
                    return '';
                },
            },
            customerPhone: {
                default: function () {
                    return '';
                },
            },
            customerNotes: {
                default: function () {
                    return '';
                },
            },
            services: {
                default: function () {
                    return [];
                },
            },
            discounts: {
                default: function () {
                    return [];
                },
            },
            status: {
                default: function () {
                    return '';
                },
            },
            isLoading: {
                default: function () {
                    return false;
                },
            },
            isSaved: {
                default: function () {
                    return false;
                },
            },
            isError: {
                default: function () {
                    return false;
                },
            },
            errorMessage: {
                default: function () {
                    return '';
                },
            },
            customFields: {
                default: function () {
                    return [];
                },
            },
            shop: {
                default: function () {
                    return {};
                },
            },
        },
        mounted() {
            this.loadDiscounts()
            this.loadAvailabilityIntervals()
            this.loadAvailabilityServices()
            this.loadCustomFields()
            this.isLoadingServicesAssistants = true
            Promise.all([
                this.loadServices(),
                this.loadAttendants(),
                this.loadServicesCategory()
            ]).then(() => {
                this.isLoadingServicesAssistants = false
                this.elServices.forEach((i, index) => {
                    this.addServicesSelectSearchInput(index)
                    this.addAssistantsSelectSearchInput(index)
                })
            })
        },
        data: function () {
            return {
                elDate: this.date,
                elTime: this.timeFormat(this.time),
                elCustomerFirstname: this.customerFirstname,
                elCustomerLastname: this.customerLastname,
                elCustomerEmail: this.customerEmail,
                elCustomerAddress: this.customerAddress,
                elCustomerPhone: this.customerPhone,
                elCustomerNotes: this.customerNotes,
                elServices: [...this.services].map(s => ({service_id: s.service_id, assistant_id: s.assistant_id})),
                elDiscounts: [...this.discounts],
                elStatus: this.status,
                visibleDiscountInfo: false,
                elDiscountsList: [],
                elServicesList: [],
                elServicesNameList:[],
                elAttendantsList: [],
                showTimeslots: false,
                availabilityIntervals: {},
                saveAsNewCustomer: false,
                availabilityServices: [],
                serviceSearch: [],
                isValid: true,
                requiredFields: [],
                visibleExtraInfo: false,
                customFieldsList: [],
                elCustomFields: this.customFields,
                isLoadingServicesAssistants: false,
                assistantSearch: [],
                availabilityAttendants: [],
            };
        },
        watch: {
            elDate() {
                this.loadAvailabilityIntervals()
                this.loadAvailabilityServices()
            },
            elTime() {
                this.loadAvailabilityServices()
            },
            shop() {
                this.loadAvailabilityIntervals()
                this.loadAvailabilityServices()
                this.isLoadingServicesAssistants = true
                Promise.all([
                    this.loadServices(),
                    this.loadAttendants()
                ]).then(() => {
                    this.isLoadingServicesAssistants = false
                    this.elServices.forEach((i, index) => {
                        this.addServicesSelectSearchInput(index)
                        this.addAssistantsSelectSearchInput(index)
                    })
                })
            },
        },
        computed: {
            statusesList() {
                var statuses = [];
                for (var key in this.$root.statusesList) {
                    statuses.push({value: key, text: this.$root.statusesList[key].label})
                }
                return statuses;
            },
            discountsList() {
                var list = [];
                this.elDiscountsList.forEach((i) => {
                    list.push({value: i.id, text: i.name})
                })
                return list;
            },
            servicesList() {
                var list = [];
                this.elServicesList.forEach((serviceItem) => {
                    let categories = [];
                    serviceItem.categories.forEach( catId => {
                        let category = this.elServicesNameList.find( item => item.id === catId)
                        if (category) {
                            categories.push(category.name)
                        }
                    })
                    let available = false
                    let availabilityService = this.availabilityServices.find( item => item.id === serviceItem.id)
                    if (availabilityService) {
                        available = availabilityService.available
                    }
                    list.push({
                        value: serviceItem.id,
                        price: serviceItem.price,
                        duration: serviceItem.duration,
                        currency: serviceItem.currency,
                        serviceName: serviceItem.name,
                        category: categories.join(', '),
                        empty_assistants: serviceItem.empty_assistants,
                        available: available,
                    })
                });

                return list;
            },
            attendantsList() {
                var list = [];
                this.elAttendantsList.forEach((i) => {
                    let available = false
                    let availabilityAttendant = this.availabilityAttendants.find( item => item.id === i.id)
                    if (availabilityAttendant) {
                        available = availabilityAttendant.available
                    }
                    list.push({value: i.id, text: i.name, available: available})
                })
                return list;
            },
            timeslots() {
                var timeslots = this.availabilityIntervals.workTimes ? Object.values(this.availabilityIntervals.workTimes) : []
                return timeslots.map(t => this.timeFormat(t))
            },
            freeTimeslots() {
                return this.availabilityIntervals.times ? Object.values(this.availabilityIntervals.times) : []
            },
            showAttendant() {
                return typeof this.$root.settings.attendant_enabled !== 'undefined' ? this.$root.settings.attendant_enabled : true;
            },
            bookingServices() {
                return JSON.parse(JSON.stringify(this.elServices)).map(s => { !s.assistant_id ? s.assistant_id = 0 : s.assistant_id; return s; })
            },
        },
        methods: {
            close() {
                this.$emit('close');
            },
            chooseCustomer() {
                this.$emit('chooseCustomer');
            },
            save() {
                this.isValid = this.validate()
                if (!this.isValid) {
                    return;
                }
                var booking = {
                    date: this.moment(this.elDate).format('YYYY-MM-DD'),
                    time: this.moment(this.elTime, this.getTimeFormat()).format('HH:mm'),
                    status: this.elStatus,
                    customer_id: this.customerID ? this.customerID : 0,
                    customer_first_name: this.elCustomerFirstname,
                    customer_last_name: this.elCustomerLastname,
                    customer_email: this.elCustomerEmail,
                    customer_phone: this.elCustomerPhone,
                    customer_address: this.elCustomerAddress,
                    services: this.bookingServices,
                    discounts: this.elDiscounts,
                    note: this.elCustomerNotes,
                    save_as_new_customer: this.saveAsNewCustomer,
                    custom_fields: this.elCustomFields,
                }

                if (this.shop) {
                    booking['shop'] = {id: this.shop.id}
                }

                this.$emit('save', booking);
            },
            loadDiscounts() {
                this.axios.get('discounts').then((response) => {
                    this.elDiscountsList = response.data.items
                })
            },
            loadServices() {
                return this.axios.get('services', { params: { per_page: -1, shop: this.shop ? this.shop.id : null } }).then((response) => {
                    this.elServicesList = response.data.items
                })
            },
            loadServicesCategory() {
              return this.axios.get('services/categories').then((response) => {
                this.elServicesNameList = response.data.items
              })
            },
            loadAttendants() {
                return this.axios.get('assistants', {params: {shop: this.shop ? this.shop.id : null}}).then((response) => {
                    this.elAttendantsList = response.data.items
                })
            },
            loadAvailabilityIntervals() {
                this.axios.post('availability/intervals', {
                    date: this.moment(this.elDate).format('YYYY-MM-DD'),
                    time: this.moment(this.elTime, this.getTimeFormat()).format('HH:mm'),
                    shop: this.shop ? this.shop.id : 0,
                }).then((response) => {
                    this.availabilityIntervals = response.data.intervals
                })
            },
            loadAvailabilityServices() {
                this.axios.post('availability/booking/services', {
                    date: this.moment(this.elDate).format('YYYY-MM-DD'),
                    time: this.moment(this.elTime, this.getTimeFormat()).format('HH:mm'),
                    booking_id: !this.bookingID ? 0 : this.bookingID,
                    is_all_services: true,
                    services: this.bookingServices.filter(i => i.service_id),
                    shop: this.shop ? this.shop.id : 0,
                }).then((response) => {
                    this.availabilityServices = response.data.services
                })
            },
            loadAvailabilityAttendants(service_id) {
                this.axios.post('availability/booking/assistants', {
                    date: this.moment(this.elDate).format('YYYY-MM-DD'),
                    time: this.moment(this.elTime, this.getTimeFormat()).format('HH:mm'),
                    booking_id: !this.bookingID ? 0 : this.bookingID,
                    selected_service_id: service_id ? service_id : 0,
                    services: this.bookingServices.filter(i => i.service_id),
                    shop: this.shop ? this.shop.id : 0,
                }).then((response) => {
                    this.availabilityAttendants = response.data.assistants
                })
            },
            loadCustomFields() {
                this.axios.get('custom-fields/booking').then((response) => {
                    this.customFieldsList = response.data.items.filter(i => ['html', 'file'].indexOf(i.type) === -1)
                })
            },
            addDiscount() {
                this.elDiscounts.push(0);
            },
            deleteDiscount(index) {
                this.elDiscounts.splice(index, 1)
            },
            addService() {
                this.elServices.push({service_id: null, assistant_id: null});
                this.addServicesSelectSearchInput(this.elServices.length - 1)
                this.addAssistantsSelectSearchInput(this.elServices.length - 1)
            },
            deleteService(index) {
                this.elServices.splice(index, 1)
                this.serviceSearch.splice(index, 1)
            },
            setTime(timeslot) {
                this.elTime = timeslot
                this.showTimeslots = false
            },
            getServicesListBySearch(list, search) {
                if (!search) {
                    return list
                }
                return list.filter(i => (new RegExp(search, 'ig')).test([i.category, i.serviceName, i.price, i.duration].join('')))
            },
            validate() {
                this.requiredFields = []
                if (!this.elDate) {
                    this.requiredFields.push('date')
                }
                if (!this.elTime.trim()) {
                    this.requiredFields.push('time')
                }
                if (!this.elCustomerFirstname.trim()) {
                    this.requiredFields.push('customer_first_name')
                }
                if (!this.bookingServices.length) {
                    this.requiredFields.push('services')
                }
                this.bookingServices.forEach((i, index)=> {
                    if (!i.service_id) {
                        this.requiredFields.push('services_service_' + index)
                    }
                    if (this.isShowAttendant(i) && !i.assistant_id) {
                        this.requiredFields.push('services_assistant_' + index)
                    }
                })
                return this.requiredFields.length === 0
            },
            isShowAttendant(service) {
                let serviceItem = this.servicesList.find((i) => i.value === service.service_id)
                if (!serviceItem) {
                    return this.showAttendant
                }
                return this.showAttendant && (!service.service_id || serviceItem && !serviceItem.empty_assistants)
            },
            updateCustomField(key, value) {
                let field = this.elCustomFields.find(i => i.key === key)
                if (field) {
                    field.value = value
                } else {
                    this.elCustomFields.push({key: key, value: value})
                }
            },
            getCustomFieldValue(key, default_value) {
                let field = this.elCustomFields.find(i => i.key === key)
                if (field) {
                    return field.value
                }
                return default_value
            },
            addServicesSelectSearchInput(index) {
                this.serviceSearch.push('')
                setTimeout(() => {
                    window.document
                        .querySelectorAll(".service .vue-dropdown")[index]
                            .prepend(window.document.querySelectorAll(".service .vue-select-search")[index])

                    let i = this.$refs['select-service'][index]

                    let blur = i.blur
                    i.blur   = () => {}

                    let focus = i.focus
                    let input = window.document.querySelectorAll(".service .vue-select-search-input")[index];
                    i.focus = () => {
                        focus()
                        setTimeout(() => {
                            input.focus()
                        }, 0)
                    }
                    input.addEventListener('blur', () => {
                        blur()
                        this.serviceSearch[index] = ''
                    })
                }, 0);
            },
            addAssistantsSelectSearchInput(index) {
                this.assistantSearch.push('')
                setTimeout(() => {
                    window.document
                        .querySelectorAll(".attendant .vue-dropdown")[index]
                            .prepend(window.document.querySelectorAll(".attendant .vue-select-search")[index])

                    let i = this.$refs['select-assistant'][index]

                    let blur = i.blur
                    i.blur   = () => {}

                    let focus = i.focus
                    let input = window.document.querySelectorAll(".attendant .vue-select-search-input")[index];
                    i.focus = () => {
                        focus()
                        setTimeout(() => {
                            input.focus()
                        }, 0)
                    }
                    input.addEventListener('blur', () => {
                        blur()
                        this.assistantSearch[index] = ''
                    })
                }, 0);
            },
            getAttendantsListBySearch(list, search) {
                if (!search) {
                    return list
                }
                return list.filter(i => (new RegExp(search, 'ig')).test([i.text].join('')))
            },
        },
        emits: ['close', 'chooseCustomer', 'save'],
        components: {
            CustomField,
        },
    }
</script>

<style scoped>
    .booking-details-customer-info,
    .booking-details-total-info,
    .booking-discount-info,
    .booking-details-status-info,
    .booking-details-extra-info {
        border: solid 1px #ccc;
        padding: 20px;
        text-align: left;
        margin-bottom: 20px;
    }
    .actions {
        text-align: right;
    }
    .date,
    .time,
    .customer-firstname,
    .customer-lastname,
    .customer-email,
    .customer-address,
    .customer-phone,
    .customer-notes,
    .service,
    .attendant,
    .discount {
        border-bottom: solid 1px #ccc;
        margin-bottom: 20px;
        padding-bottom: 5px;
    }
    .booking-details-status-info .row {
        align-items: center;
    }
    .fa-circle-xmark {
        cursor: pointer;
    }
    .select-existing-client {
        margin-bottom: 20px;
    }
    .alert {
        padding: 6px 12px;
        margin-bottom: 0;
    }
    .spinner-border {
        vertical-align: middle;
    }
    .discount-row {
        align-items: center;
    }
    .service-row {
        align-items: baseline;
    }
    .timeslots {
        width: 50%;
        height: 200px;
        position: absolute;
        z-index: 100000;
        background-color: white;
        top: 40px;
        display: flex;
        border: solid 1px #ccc;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 20px;
        flex-wrap: wrap;
    }
    .time {
        position: relative;
    }
    .timeslot {
        padding: 10px;
        color: #dc3545;
        cursor: pointer;
    }
    .timeslot.free {
        color: #28a745;
    }
    .timeslots.hide {
        display: none;
    }
    .timeslot-input {
        width: 100%;
        max-width: 274px;
    }
    .input-group {
        flex-wrap: nowrap;
    }
    .form-control option{
      display:flex;
      justify-content: space-between;
      align-items: center;
    }
    .service-select{
      width:100%;
      font-size: 1rem;
      color: #212529;

      line-height:1.5;
      border-radius: .375rem;
    }

    .option-item{
      display:flex;
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
      color:#637491;
      padding:4px;
    }
    .option-item-selected {
      color: #000;
      width: 100%;
      padding-right: 10px;
      padding-left: 10px;
    }
    .form-switch {
        display: flex;
        justify-content: space-between;
        flex-direction: row-reverse;
        padding-left: 0;
        align-items: center;
    }
    .form-switch :deep(.form-check-input) {
        width: 3em;
        height: 1.5em;
    }
    .vue-select-search {
        display: none;
        position: relative;
        margin-top: 10px;
        margin-bottom: 20px;
    }
    .vue-dropdown .vue-select-search {
        display: list-item;
    }
    .vue-select-search-icon {
        position: absolute;
        z-index: 1000;
        top: 12px;
        left: 15px;
        color: #7F8CA2;
    }
    .vue-select-search-input {
        padding-left: 40px;
        padding-right: 20px;
        border-radius: 30px;
        border-color: #fff;
    }
    .service-select :deep(.vue-dropdown) {
        padding-top: 15px;
        padding-bottom: 15px;
    }
    .availability-wrapper {
        display: flex;
        align-items: center;
    }
    .availability {
        width: 10px;
        height: 10px;
        margin-right: 10px;
        background-color: #9F0404;
        border-radius: 10px;
    }
    .availability.available {
        background-color: #1EAD3F;
    }
    .service-name {
        font-weight: bold;
    }
    .required {
        border: solid 1px #9F0404;
    }
    .add-service-wrapper {
        display: flex;
    }
    .add-service-required {
        margin-left: 10px;
    }
    .booking-details-extra-info-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .booking-details-extra-info-header-btn {
        font-size: 22px;
        color: #0d6efd;
    }
    .services-assistants-loader {
        margin-left: 20px;
    }
    @media (max-width: 576px) {
        .status {
            margin-bottom: 10px;
        }
        .timeslot-input {
            max-width: 100%;
        }
        .timeslots {
            width: 100%;
        }
        .service-row,
        .discount-row {
            width: 100%;
            position: relative;
        }
        .service-row-delete {
            position: absolute;
            top: 30%;
            text-align: right;
            right: -20px;
            width: 30px;
        }
        .discount-row-delete {
            position: absolute;
            text-align: right;
            top: 40%;
            right: -20px;
            width: 30px;
        }
        .save-button-wrapper {
            width: 60%;
        }
        .save-button-result-wrapper {
            width: 40%;
            text-align: center;
        }
        :deep(.vue-dropdown) {
            left: -50px;
            width: calc(100vw - 25px);
        }
    }
</style>

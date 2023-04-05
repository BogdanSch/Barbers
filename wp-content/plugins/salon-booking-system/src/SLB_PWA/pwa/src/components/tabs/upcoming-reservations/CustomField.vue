<template>
    <b-row class="field">
        <b-col sm="12">
            <template v-if="type === 'text'">
                <b-form-input v-model.lazy="elValue" :id="key" />
                <label class="label" :for="key">{{ label }}</label>
            </template>
            <template v-if="type === 'textarea'">
                <b-form-textarea v-model.lazy="elValue" :id="key"></b-form-textarea>
                <label class="label" :for="key">{{ label }}</label>
            </template>
            <template v-if="type === 'checkbox'">
                <b-form-checkbox v-model="elValue" :id="key">
                    {{ label }}
                </b-form-checkbox>
            </template>
            <template v-if="type === 'select'">
                <b-form-select v-model="elValue" :id="key" :options="options"></b-form-select>
                <label class="label" :for="key">{{ label }}</label>
            </template>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        name: 'CustomField',
        props: {
            field: {
                default: function () {
                    return {};
                },
            },
            value: {
                default: function () {
                    return '';
                },
            },
        },
        mounted() {
            this.update()
        },
        data: function () {
            let value = this.value
            if (this.field.type === 'checkbox') {
                value = !!value
            }
            return {
                elValue: value
            }
        },
        watch: {
            elValue() {
                this.update()
            }
        },
        computed: {
            key() {
                return this.field.key
            },
            type() {
                return this.field.type
            },
            label() {
                return this.field.label
            },
            options() {
                return this.field.options.map(i => ({value: i.value, text: i.label}))
            },
        },
        methods: {
            update() {
                this.$emit('update', this.key, this.elValue);
            },
        },
        emits: ['update']
    }
</script>

<style scoped>
    .field + .field {
        margin-top: 10px;
    }
    .label {
        color: #888;
        font-size: 14px;
    }
</style>

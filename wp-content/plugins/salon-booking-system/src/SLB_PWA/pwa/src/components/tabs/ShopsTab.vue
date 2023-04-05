<template>
    <div>
        <ShopsList @shopsEnabled="shopsEnable" @applyShop="applyShop"/>
    </div>
</template>

<script>

    import ShopsList from './shops/ShopsList.vue'

    export default {
        name: 'ShopsTab',
        components: {
            ShopsList,
        },
        methods: {
            shopsEnable(enabled) {
                this.$emit('shopsEnabled', enabled)
            },
            applyShop(shop) {
                this.$emit('applyShop', shop)
            },
        },
        emits:['shopsEnabled', 'applyShop']
    }
</script>

<style scoped>

</style>
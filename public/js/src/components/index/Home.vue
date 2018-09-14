<template src="@/templates/index/home.html" />

<script>
    import StrategiesList from '@/components/strategy/List'

    export default {
        name: "Home",
        components: { StrategiesList },
        mounted() {
            //this.$store.commit('setPageTitle', 'Login page')
        }
    }
</script>

<style scoped>

</style>
<template src="@/templates/index/home.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "Home",
        data() {
            return {
                strategies: []
            }
        },
        mounted() {
            Api.methods.request('app_homepage', {}, 'GET', response => {
                this.strategies = response
                this.$store.commit('setPageTitle', 'Strategies')
            })
        }
    }
</script>

<style scoped>

</style>
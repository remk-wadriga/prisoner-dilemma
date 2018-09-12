<template src="@/templates/strategy/list.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "List",
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
<template src="@/templates/index/home.html" />

<script>
    import Api from '@/helpers/Api'
    //import user from '@/stores/User'

    export default {
        name: "Home",
        data() {
            return {
                strategies: []
            }
        },
        methods: {

        },
        mounted() {
            Api.methods.request('app_homepage', {}, 'GET', response => {
                this.strategies = response;
            })
        }
    }
</script>

<style scoped>

</style>
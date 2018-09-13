<template src="@/templates/strategy/list.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "List",
        data() {
            return {
                strategies: [],
                fields: ['name', 'description', 'status']
            }
        },
        methods: {
            selectStrategy(id) {
                this.$router.push({name: 'strategy_view', params: {id: id}})
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
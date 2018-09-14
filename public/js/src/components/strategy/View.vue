<template src="@/templates/strategy/view.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "View",
        data() {
            return {
                strategy: null
            }
        },
        methods: {
            deleteStrategy() {
                console.log('Delete')
            }
        },
        mounted() {
            const id = this.$route.params.id
            Api.methods.request(['strategy_url', {id}], {}, 'GET', response => {
                this.strategy = response
                this.$store.commit('setContentTitle', 'Strategy "' + this.strategy.name + '"')
                this.$store.commit('setBreadcrumbs', [
                    {title: 'Strategies', url: 'app_homepage'},
                    {title: this.strategy.name, url: {name: 'strategy_view', params: {id}}}
                ])
                this.$store.commit('setPageTopButtons', [
                    {title: 'Update', type: 'primary', click: {url: {name: 'strategy_update', params: {id}}}},
                    {title: 'Delete', type: 'danger', click: 'openDeleteStrategyModal'}
                ])
                this.$store.commit('selectedStrategy', this.strategy)
            })
        }
    }
</script>

<style scoped>

</style>
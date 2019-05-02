<template src="@/templates/strategy/update.html" />

<script>
    import Api from '@/helpers/Api'
    import Form from '@/components/strategy/Form'

    export default {
        name: "StrategyUpdate",
        components: { Form },
        data() {
            return {
                strategy: null
            }
        },
        methods: {

        },
        mounted() {
            let id = this.$route.params.id
            Api.methods.request(['strategy_url', {id}], {}, 'GET', response => {
                this.strategy = response
                this.$store.commit('setContentTitle', 'Update strategy "' + this.strategy.name + '"')
                this.$store.commit('setBreadcrumbs', [
                    {title: 'Strategies', url: 'app_homepage'},
                    {title: this.strategy.name, url: {name: 'strategy_view', params: {id}}},
                    {title: 'Update', url: {name: 'strategy_update', params: {id}}},
                ])
                this.$store.commit('setPageTopButtons', [
                    {title: 'Show statistics', type: 'success', click: {url: {name: 'strategy_statistics', params: {id}}}}
                ])
                this.$store.commit('selectedStrategyId', id)
            })
        }
    }
</script>

<style scoped>

</style>
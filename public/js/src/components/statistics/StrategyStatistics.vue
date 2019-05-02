<template src="@/templates/statistics/strategy-statistics.html" />

<script>
    import Api from '@/helpers/Api.js'

    export default {
        name: "StrategyStatistics",
        data() {
            return {
                strategy: null,
                statistics: null
            }
        },
        methods: {

        },
        mounted() {
            const id = this.$route.params.id

            Api.methods.request(['strategy_statistics_url', {id}], {}, 'GET', response => {
                this.strategy = response.strategy
                this.statistics = response.statistics

                this.$store.commit('setContentTitle', 'Strategy "' + this.strategy.name + '"')
                this.$store.commit('setBreadcrumbs', [
                    {title: 'Strategies', url: 'app_homepage'},
                    {title: this.strategy.name, url: {name: 'strategy_view', params: {id}}},
                    {title: 'Strategy statistics', url: {name: 'strategy_statistics', params: {id}}}
                ])
                this.$store.commit('setPageTopButtons', [])

                this.$store.commit('selectedStrategyId', this.strategy.id)


                console.log(response.statistics)
            })
        }
    }
</script>

<style scoped>

</style>
<template src="@/templates/statistics/strategy/strategy-statistics.html" />

<script>
    import Api from '@/helpers/Api.js'
    import StrategyStatisticsByDates from '@/components/statistics/strategy/StrategyStatisticsByDates'
    import StrategyStatisticsByRoundsCount from '@/components/statistics/strategy/StrategyStatisticsByRoundsCount'

    export default {
        name: "StrategyStatistics",
        components: { StrategyStatisticsByDates, StrategyStatisticsByRoundsCount },
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

            Api.methods.request(['strategy_url', {id}], {}, 'GET', response => {
                this.strategy = response

                this.$store.commit('setContentTitle', 'Strategy "' + this.strategy.name + '" statistics')
                this.$store.commit('setBreadcrumbs', [
                    {title: 'Strategies', url: 'app_homepage'},
                    {title: this.strategy.name, url: {name: 'strategy_view', params: {id}}},
                    {title: 'Strategy statistics', url: {name: 'strategy_statistics', params: {id}}}
                ])
                this.$store.commit('setPageTopButtons', [])

                this.$store.commit('selectedStrategyId', this.strategy.id)
            })
        }
    }
</script>

<style scoped>

</style>
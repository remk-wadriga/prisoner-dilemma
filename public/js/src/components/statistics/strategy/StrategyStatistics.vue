<template src="@/templates/statistics/strategy/strategy-statistics.html" />

<script>
    import Api from '@/helpers/Api.js'
    import Formatter from '@/helpers/Formatter'
    import StrategyStatisticsByDates from '@/components/statistics/strategy/StrategyStatisticsByDates'
    import StrategyStatisticsByRoundsCount from '@/components/statistics/strategy/StrategyStatisticsByRoundsCount'
    import DateRangePicker from '@/components/DateRangePicker'

    export default {
        name: "StrategyStatistics",
        components: { StrategyStatisticsByDates, StrategyStatisticsByRoundsCount, DateRangePicker },
        data() {
            return {
                isReady: false,
                strategy: null,
                lazyLoad: true,
                selectedDates: {start: null, end: null}
            }
        },
        watch: {
            selectedDates() {
                // Clear all statistics cash
                this.$store.commit('setStatistics', null)
            }
        },
        methods: {
            setDatesRange(range) {
                this.selectedDates = range
            }
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

                // Get strategy statistics dates range
                const storeID = 'strategyStatisticsDates_' + id
                let statisticsDates = this.$store.state.statistics[storeID]
                if (statisticsDates !== undefined) {
                    this.selectedDates = statisticsDates
                    this.isReady = true
                } else {
                    Api.methods.request(['strategy_statistics_dates', {id}], {}, 'GET', response => {
                        if (response) {
                            const selectedDates = {
                                start: Formatter.methods.formatDate(response.start),
                                end: Formatter.methods.formatDate(response.end)
                            }
                            this.$store.commit('setStatistics', {id: storeID, data: selectedDates})
                            this.selectedDates = selectedDates
                        }
                        this.isReady = true
                    })
                }
            })
        }
    }
</script>

<style scoped>

</style>
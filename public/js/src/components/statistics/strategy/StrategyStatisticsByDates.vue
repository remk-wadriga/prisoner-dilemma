<template src="@/templates/statistics/strategy/strategy-statistics-by-dates.html" />

<script>
    import Api from '@/helpers/Api.js'

    export default {
        name: "StrategyStatisticsByDates",
        props: {
            strategy: Object
        },
        data() {
            return {
                isReady: false
            }
        },
        methods: {
            getStatistics(callback, setIsReady = false) {
                let statisticsID = 'strategyStatisticsByDates_' + this.strategy.id
                let statistics = this.$store.state.statistics[statisticsID]
                if (statistics === undefined || statistics === null) {
                    Api.methods.request(['strategy_statistics_by_dates_url', {id: this.strategy.id}], {}, 'GET', response => {
                        this.$store.commit('setStatistics', {id: statisticsID, data: response})
                        if (callback !== undefined && callback !== null) {
                            callback(response)
                        }
                        if (setIsReady) {
                            this.isReady = true
                        }
                    })
                } else {
                    if (setIsReady) {
                        this.isReady = true
                    }
                    if (callback !== undefined && callback !== null) {
                        callback(statistics)
                    }
                }
                return statistics ? statistics : [];
            }
        },
        computed: {
            statistics() {
                return this.getStatistics()
            }
        },
        mounted() {
            return this.getStatistics(null, true)
        }
    }
</script>

<style scoped>

</style>
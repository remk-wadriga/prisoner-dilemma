<template src="@/templates/statistics/strategy/strategy-statistics-by-dates.html" />

<script>
    import Api from '@/helpers/Api.js'
    import LineChart from '@/components/charts/LineChart'

    export default {
        name: "StrategyStatisticsByDates",
        props: {
            strategy: Object
        },
        components: { LineChart },
        data() {
            return {
                isReady: false,
                statistics: null,
                chartLabels: [],
                chartData: [],
                chartOptions: null,
                chartTooltipTitleCallback: null,
                chartTooltipLabelCallback: null
            }
        },
        methods: {
            init (statistics) {
                this.statistics = statistics

                let bales = []
                let gamesCount = []

                this.statistics.forEach(data => {
                    this.chartLabels.push(data.gameDate)
                    bales.push(data.bales)
                    gamesCount.push(data.gamesCount)
                })

                this.chartData = [
                    {
                        label: 'Bales',
                        data: bales
                    }
                ]

                this.chartTooltipLabelCallback = (item) => {
                    return 'Bales: ' + bales[item.index] + ' Games count: ' + gamesCount[item.index]
                }

                this.isReady = true
            }
        },
        mounted() {
            let statisticsID = 'strategyStatisticsByDates_' + this.strategy.id
            let statistics = this.$store.state.statistics[statisticsID]

            if (statistics) {
                this.init(statistics)
            } else {
                Api.methods.request(['strategy_statistics_by_dates_url', {id: this.strategy.id}], {}, 'GET', response => {
                    this.$store.commit('setStatistics', {id: statisticsID, data: response})
                    this.init(response)
                })
            }
        }
    }
</script>

<style scoped>
</style>
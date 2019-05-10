<template src="@/templates/statistics/strategy/strategy-statistics-by-dates.html" />

<script>
    import Api from '@/helpers/Api.js'
    import LineChart from '@/components/charts/LineChart'

    export default {
        name: "StrategyStatisticsByDates",
        components: { LineChart },
        data() {
            return {
                isReady: false,
                chartLabels: [],
                chartData: [],
                chartOptions: null,
                chartTooltipTitleCallback: null,
                chartTooltipLabelCallback: null
            }
        },
        props: {
            strategy: Object,
            selectedDates: Object
        },
        watch: {
            selectedDates() {
                // Refresh page
                this.refreshData()
            }
        },
        methods: {
            init (statistics) {
                let bales = []
                let gamesCount = []
                this.chartLabels = []

                statistics.forEach(data => {
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

                this.chartTooltipLabelCallback = item => {
                    return 'Bales: ' + bales[item.index] + '; Games count: ' + gamesCount[item.index]
                }

                this.isReady = true
            },
            refreshData() {
                let params = {id: this.strategy.id}
                const statisticsID = 'strategyStatisticsByDates_' + this.strategy.id
                if (this.selectedDates) {
                    params.fromDate = this.selectedDates.start
                    params.toDate = this.selectedDates.end
                }
                Api.methods.request(['strategy_statistics_by_dates_url', params], {}, 'GET', response => {
                    this.$store.commit('setStatistics', {id: statisticsID, data: response})
                    this.init(response)
                })
            }
        },
        mounted() {
            const statisticsID = 'strategyStatisticsByDates_' + this.strategy.id
            let statistics = this.$store.state.statistics[statisticsID]

            if (statistics) {
                this.init(statistics)
            } else {
                this.refreshData()
            }
        }
    }
</script>

<style scoped>
</style>
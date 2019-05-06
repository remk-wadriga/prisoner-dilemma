<template src="@/templates/statistics/total/total-statistics-by-dates.html" />

<script>
    import Api from '@/helpers/Api.js'
    import LineChart from '@/components/charts/LineChart'

    export default {
        name: "TotalStatisticsByDates",
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
        methods: {
            init (statistics) {
                let bales = []
                let gamesCount = []
                let roundsCount = []

                statistics.forEach(data => {
                    this.chartLabels.push(data.gameDate)
                    bales.push(data.bales)
                    roundsCount.push(data.roundsCount)
                })

                this.chartData = [
                    {
                        label: 'Bales',
                        data: bales
                    }
                ]

                this.chartTooltipLabelCallback = item => {
                    return 'Bales: ' + bales[item.index] + '; Games count: ' + gamesCount[item.index] + '; Rounds count: ' + roundsCount[item.index]
                }

                this.isReady = true
            }
        },
        mounted() {
            let statisticsID = 'totalStatisticsByDates'
            let statistics = this.$store.state.statistics[statisticsID]

            if (statistics) {
                this.init(statistics)
            } else {
                Api.methods.request('total_statistics_by_dates_url', {}, 'GET', response => {
                    this.$store.commit('setStatistics', {id: statisticsID, data: response})
                    this.init(response)
                })
            }
        }
    }
</script>

<style scoped>

</style>
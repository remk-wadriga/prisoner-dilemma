<template src="@/templates/statistics/total/total-statistics-by-rounds-count.html" />

<script>
    import Api from '@/helpers/Api.js'
    import LineChart from '@/components/charts/LineChart'

    export default {
        name: "TotalStatisticsByRoundsCount",
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

                statistics.forEach(data => {
                    this.chartLabels.push(data.roundsCount)
                    bales.push(data.bales)
                    gamesCount.push(data.gamesCount)
                })

                this.chartData = [
                    {
                        label: 'Bales',
                        data: bales
                    }
                ]

                this.chartTooltipTitleCallback = item => {
                    return 'Rounds: ' + this.chartLabels[item[0].index]
                }
                this.chartTooltipLabelCallback = item => {
                    return 'Bales: ' + bales[item.index] + '; Games count: ' + gamesCount[item.index]
                }

                this.isReady = true
            }
        },
        mounted() {
            let statisticsID = 'totalStatisticsByRoundsCount'
            let statistics = this.$store.state.statistics[statisticsID]

            if (statistics) {
                this.init(statistics)
            } else {
                Api.methods.request('total_statistics_by_rounds_count_url', {}, 'GET', response => {
                    this.$store.commit('setStatistics', {id: statisticsID, data: response})
                    this.init(response)
                })
            }
        }
    }
</script>

<style scoped>

</style>
<template src="@/templates/statistics/total/total-statistics-by-games.html" />

<script>
    import Api from '@/helpers/Api.js'
    import BarChart from '@/components/charts/BarChart'

    export default {
        name: "TotalStatisticsByGames",
        components: { BarChart },
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
                let roundsCount = []
                let winners = []
                let losers = []

                statistics.forEach(data => {
                    this.chartLabels.push(data.game + ' (' + data.gameDate + ')')
                    bales.push(data.bales)
                    roundsCount.push(data.roundsCount)
                    winners.push(data.winner)
                    losers.push(data.loser)
                })

                this.chartData = [
                    {
                        label: 'Bales',
                        data: bales
                    }
                ]

                this.chartTooltipTitleCallback = item => {
                    return 'Game: ' + this.chartLabels[item[0].index]
                }
                this.chartTooltipLabelCallback = item => {
                    return 'Bales: ' + bales[item.index]
                        + '; Rounds count: ' + roundsCount[item.index]
                        + '; Winner: ' + winners[item.index].strategy + ' (' + winners[item.index].bales + ')'
                        + '; Loser: ' + losers[item.index].strategy + ' (' + losers[item.index].bales + ')'
                }

                this.isReady = true
            }
        },
        mounted() {
            let statisticsID = 'totalStatisticsByGames'
            let statistics = this.$store.state.statistics[statisticsID]

            if (statistics) {
                this.init(statistics)
            } else {
                Api.methods.request('total_statistics_by_games_url', {}, 'GET', response => {
                    this.$store.commit('setStatistics', {id: statisticsID, data: response})
                    this.init(response)
                })
            }
        }
    }
</script>

<style scoped>

</style>
<template src="@/templates/statistics/game/game-statistics-by-dates.html" />

<script>
    import Api from '@/helpers/Api.js'
    import BarChart from '@/components/charts/BarChart'

    export default {
        name: "GameStatisticsByDates",
        props: {
            game: Object
        },
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
                let roundsOunt = []
                let winners = []
                let losers = []

                statistics.forEach(data => {
                    this.chartLabels.push(data.gameDate)
                    bales.push(data.bales)
                    roundsOunt.push(data.roundsCount)
                    winners.push(data.winner)
                    losers.push(data.loser)
                })

                this.chartData = [
                    {
                        label: 'Bales',
                        data: bales
                    }
                ]

                this.chartTooltipLabelCallback = item => {
                    return 'Bales: ' + bales[item.index]
                        + '; Rounds count: ' + roundsOunt[item.index]
                        + '; Winner: ' + winners[item.index].strategy + ' (' + winners[item.index].bales + ')'
                        + '; Loser: ' + losers[item.index].strategy + ' (' + losers[item.index].bales + ')'
                }

                this.isReady = true
            }
        },
        mounted() {
            let statisticsID = 'gameStatisticsByDates_' + this.game.id
            let statistics = this.$store.state.statistics[statisticsID]

            if (statistics) {
                this.init(statistics)
            } else {
                Api.methods.request(['game_statistics_by_dates_url', {id: this.game.id}], {}, 'GET', response => {
                    this.$store.commit('setStatistics', {id: statisticsID, data: response})
                    this.init(response)
                })
            }
        }
    }
</script>

<style scoped>

</style>
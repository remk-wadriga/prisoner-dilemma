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
        props: {
            selectedDates: Object,
            gameParamsFilters: Object
        },
        watch: {
            selectedDates() {
                this.refreshData()
            },
            gameParamsFilters() {
                this.refreshData()
            }
        },
        methods: {
            init (statistics) {
                let bales = []
                let roundsCount = []
                let winners = []
                let losers = []
                this.chartLabels = []

                statistics.forEach(data => {
                    this.chartLabels.push(data.game)
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

                this.chartTooltipLabelCallback = item => {
                    return 'Bales: ' + bales[item.index]
                        + '; Rounds count: ' + roundsCount[item.index]
                        + '; Winner: ' + winners[item.index].strategy + ' (' + winners[item.index].bales + ')'
                        + '; Loser: ' + losers[item.index].strategy + ' (' + losers[item.index].bales + ')'
                }

                this.isReady = true
            },
            refreshData() {
                let params = this.gameParamsFilters ? this.gameParamsFilters : {}
                if (this.selectedDates) {
                    params.fromDate = this.selectedDates.start
                    params.toDate = this.selectedDates.end
                }
                Api.methods.request(['total_statistics_by_games_url', params], {}, 'GET', response => {
                    this.$store.commit('setStatistics', {id: 'totalStatisticsByGames', data: response})
                    this.init(response)
                })
            }
        },
        mounted() {
            let statistics = this.$store.state.statistics['totalStatisticsByGames']
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
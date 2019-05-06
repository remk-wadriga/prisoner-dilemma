<template src="@/templates/statistics/game/game-statistics-by-strategies.html" />

<script>
    import Api from '@/helpers/Api.js'
    import BarChart from '@/components/charts/BarChart'

    export default {
        name: "GameStatisticsByStrategies",
        props: {
            game: Object
        },
        components: { BarChart },
        data() {
            return {
                isReady: false,
                statistics: null,
                chartLabels: [],
                chartData: []
            }
        },
        methods: {
            init (statistics) {
                this.statistics = statistics

                let bales = []

                this.statistics.forEach(data => {
                    this.chartLabels.push(data.strategy)
                    bales.push(data.bales)
                })

                this.chartData = [
                    {
                        label: 'Bales',
                        data: bales
                    }
                ]

                this.isReady = true
            }
        },
        mounted() {
            let statisticsID = 'gameStatisticsByStrategies_' + this.game.id
            let statistics = this.$store.state.statistics[statisticsID]

            if (statistics) {
                this.init(statistics)
            } else {
                Api.methods.request(['game_statistics_by_strategies_url', {id: this.game.id}], {}, 'GET', response => {
                    this.$store.commit('setStatistics', {id: statisticsID, data: response})
                    this.init(response)
                })
            }
        }
    }
</script>

<style scoped>

</style>
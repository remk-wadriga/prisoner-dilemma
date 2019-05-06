<template src="@/templates/statistics/game/game-statistics-by-strategies.html" />

<script>
    import Api from '@/helpers/Api.js'

    export default {
        name: "GameStatisticsByStrategies",
        props: {
            game: Object
        },
        components: {  },
        data() {
            return {
                isReady: false,
                statistics: null
            }
        },
        methods: {
            init (statistics) {
                this.statistics = statistics

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
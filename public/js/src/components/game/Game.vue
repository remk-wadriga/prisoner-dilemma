<template src="@/templates/game/game.html" />

<script>
    import Api from '@/helpers/Api.js'

    export default {
        name: "Game",
        data() {
            return {
                strategies: [],
                params: {
                    rounds: 100,
                    balesForWin: 100,
                    balesForLoos: -50,
                    balesForCooperation: 50,
                    balesForDraw: 0
                },
                writeCoupesStrategiesResults: true
            }
        },
        mounted() {
            this.strategies = this.$store.state.strategy.checked
            Api.methods.request('params_game_url', {}, 'GET', response => {
                this.params = response
            })

            /*Api.methods.request('start_game_url', data, 'POST', response => {
                console.log(response)
            })*/



        }
    }
</script>

<style scoped>

</style>
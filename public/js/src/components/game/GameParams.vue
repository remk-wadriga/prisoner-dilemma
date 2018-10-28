<template src="@/templates/game/game-params.html" />

<script>
    import Api from '@/helpers/Api.js'

    export default {
        name: "GameParams",
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
                checkedStrategiesIds: [],
                checkedStrategiesIdsOptions: [],
                writeIndividualResults: true,
                gameResults: null,
                isReady: false
            }
        },
        methods: {
            startGame() {
                this.gameResults = null

                this.params.strategiesIds = this.checkedStrategiesIds
                this.params.writeIndividualResults = this.writeIndividualResults

                Api.methods.request('start_game_url', this.params, 'POST', response => {
                    this.gameResults = response
                })
            }
        },
        watch: {
            gameResults: {
                handler: function() {
                    this.$emit('setGameResults', this.gameResults)
                },
                deep: true
            }
        },
        mounted() {
            this.strategies = this.$store.state.strategy.checked

            this.$store.commit('setPageTitle', '')
            this.$store.commit('setContentTitle', 'Start game')
            this.$store.commit('setBreadcrumbs', [{title: 'Strategies', url: 'app_homepage'}, {title: 'Game', url: 'game_start'}])
            this.$store.commit('setPageTopButtons', [])

            Api.methods.request('params_game_url', {}, 'GET', response => {
                this.params = response

                this.strategies.forEach(strategy => {
                    this.checkedStrategiesIds.push(strategy.id)
                    this.checkedStrategiesIdsOptions.push({
                        value: strategy.id,
                        text: strategy.name
                    })
                })

                this.isReady = true
            })
        }
    }
</script>

<style scoped>

</style>
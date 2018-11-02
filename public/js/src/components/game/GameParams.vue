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
        props: {
            game: Object,
            gameParams: Object,
            results: Object
        },
        methods: {
            startGame() {
                this.gameResults = null

                this.params.strategiesIds = this.checkedStrategiesIds
                this.params.writeIndividualResults = this.writeIndividualResults

                Api.methods.request('start_game_url', this.params, 'POST', response => {
                    this.gameResults = response
                    this.$emit('setGameResults', this.gameResults)
                })
            }
        },
        mounted() {
            this.strategies = this.$store.state.strategy.checked

            let initStrategiesCheckList = () => {
                this.strategies.forEach(strategy => {
                    this.checkedStrategiesIds.push(strategy.id)
                    this.checkedStrategiesIdsOptions.push({
                        value: strategy.id,
                        text: strategy.name
                    })
                })
            }

            let getPramsCallback = () => {
                Api.methods.request('params_game_url', {}, 'GET', response => {
                    this.params = response
                    initStrategiesCheckList()
                    this.isReady = true
                })
            }

            if (this.gameParams !== null) {
                this.params = this.gameParams
            }
            if (this.results !== null) {
                console.log(this.results)
            }

            if (this.strategies.length === 0) {
                Api.methods.request('app_homepage', {}, 'GET', response => {
                    this.strategies = response
                    if (this.gameParams === null) {
                        getPramsCallback()
                    } else {
                        initStrategiesCheckList()
                        this.isReady = true
                    }
                })
            }
        }
    }
</script>

<style scoped>

</style>
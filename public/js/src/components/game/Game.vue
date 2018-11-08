<template src="@/templates/game/game.html" />

<script>
    import GameParams from '@/components/game/GameParams'
    import GameResults from '@/components/game/GameResults'
    import Api from '@/helpers/Api.js'

    export default {
        name: "Game",
        components: { GameParams, GameResults },
        data() {
            return {
                game: null,
                gameResults: null,
                gameParams: null,
                showParams: false,
                tmp_results: null
            }
        },
        methods: {
            rebuild () {
                if (this.gameResults !== null) {
                    this.tmp_results = this.gameResults
                    this.gameResults = null
                } else {
                    this.gameResults = this.tmp_results
                }
            },
            setGameResults (data) {
                this.gameResults = data
            },
            saveGame (data) {
                let cllback = response => {
                    this.$router.push({name: 'game_view', params: {id: response.info.id}})

                    this.game = response.info
                    this.gameParams = response.params

                    this.$store.commit('setContentTitle', 'Game "' + this.game.name + '"')
                }
                if (this.game === null) {
                    Api.methods.request('save_game_url', {game_form: data}, 'POST', cllback)
                } else {
                    Api.methods.request(['game_url', {id: this.game.id}], {game_form: data}, 'PUT', cllback)
                }
            }
        },
        mounted() {
            const id = this.$route.params.id

            this.$store.commit('setPageTitle', '')
            this.$store.commit('setContentTitle', this.game !== null ? this.game.name : 'Start game')
            this.$store.commit('setBreadcrumbs', [{title: 'Games', url: 'games_list'}, {title: 'Game', url: 'game_start'}])
            this.$store.commit('setPageTopButtons', [])

            if (id) {
                let getResultsCallback = () => {
                    Api.methods.request(['game_results_url', {id}], {}, 'GET', response => {
                        this.gameResults = response
                        this.gameResults.params = this.gameParams
                        this.showParams = true
                    })
                }

                Api.methods.request(['game_url', {id}], {}, 'GET', response => {
                    this.game = response.info
                    this.gameParams = response.params
                    if (response.results !== undefined) {
                        this.gameResults = response.results
                        this.gameResults.params = this.gameParams
                    }
                    this.$store.commit('setContentTitle', 'Game "' + this.game.name + '"')
                    if (this.gameResults === null) {
                        getResultsCallback()
                    } else {
                        this.showParams = true
                    }
                })
            } else {
                this.showParams = true
            }
        }
    }
</script>

<style scoped>

</style>
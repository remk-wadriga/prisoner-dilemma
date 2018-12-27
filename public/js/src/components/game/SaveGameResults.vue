<template src="@/templates/game/save-game-results.html" />

<script>
    import moment from 'moment';
    import Config from '@/config.js'

    export default {
        name: "SaveGameResults",
        data () {
            return {
                params: {
                    name: null,
                    description: null
                }
            }
        },
        props: {
            game: Object,
            sum: Number,
            totalResults: Array,
            individualResults: Object,
            winner: Object,
            looser: Object,
            onCloseCallback: Function,
            gameResultsChanged: Boolean
        },
        methods: {
            save () {
                this.$refs.saveGameResultsModalRef.hide()
                this.onCloseCallback(this.params)
            },
            close () {
                this.$refs.saveGameResultsModalRef.hide()
                this.onCloseCallback(null)
            },
            setDefaultParams (attributes) {
                const dateString = moment().format(Config.params.dateTimeFormat)

                if (attributes.name !== undefined) {
                    this.params.name = 'Game #' + dateString + ' (' + this.totalResults.length + ' strategies, ' + this.sum + ' balles)'
                }
                if (attributes.description !== undefined) {
                    this.params.description = '* Date: ' + dateString + '\n'
                        + '* Sum: ' + this.sum + '\n'
                        + '* Winner: ' + this.winner.name + ' (' + this.winner.result + ')\n'
                        + '* Looser: ' + this.looser.name + ' (' + this.looser.result + ')\n'
                        + '* Strategies:\n'

                    this.totalResults.forEach(res => {
                        this.params.description += '     #' + res.id + ' ' + res.name + ' (' + res.result + ')\n'
                    })
                }
            }
        },
        mounted() {
            if (this.gameResultsChanged) {
                this.setDefaultParams({name: true, description: true})
            } else {
                if (this.game) {
                    this.params.name = this.game.name
                    this.params.description = this.game.description
                }

                let changeParams = {};
                if (this.params.name === null) {
                    changeParams['name'] = true;
                }
                if (this.params.description === null) {
                    changeParams['description'] = true;
                }
                if (changeParams.name !== undefined || changeParams.description !== undefined) {
                    this.setDefaultParams(changeParams)
                }
            }

            this.$refs.saveGameResultsModalRef.show()
        }
    }
</script>

<style scoped>

</style>
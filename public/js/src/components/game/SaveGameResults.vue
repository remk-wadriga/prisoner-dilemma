<template src="@/templates/game/save-game-results.html" />

<script>
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
            sum: Number,
            totalResults: Array,
            individualResults: Object,
            winner: Object,
            looser: Object,
            onCloseCallback: Function
        },
        methods: {
            save () {
                this.$refs.saveGameResultsModalRef.hide()
                this.onCloseCallback(this.params)
            },
            close () {
                this.$refs.saveGameResultsModalRef.hide()
                this.onCloseCallback(null)
            }
        },
        mounted() {
            const date = new Date();
            let month = date.getMonth()
            if (month < 10) {
                month = '0' + month
            }
            let day = date.getDate()
            if (day < 10) {
                day = '0' + day
            }
            let hours = date.getHours()
            if (hours < 10) {
                hours = '0' + hours
            }
            let minutes = date.getMinutes()
            if (minutes < 10) {
                minutes = '0' + minutes
            }
            let seconds = date.getSeconds()
            if (seconds < 10) {
                seconds = '0' + seconds
            }
            const dateString = day + '-' + month + '-' + date.getFullYear() + ' ' + hours + ':' + minutes + ':' + seconds

            if (this.params.name === null) {
                this.params.name = 'Game #' + dateString + ' (' + this.totalResults.length + ' strategies, ' + this.sum + ' balles)'
            }
            if (this.params.description === null) {
                this.params.description = '* Date: ' + dateString + '\n'
                    + '* Sum: ' + this.sum + '\n'
                    + '* Winner: ' + this.winner.name + ' (' + this.winner.result + ')\n'
                    + '* Looser: ' + this.looser.name + ' (' + this.looser.result + ')\n'
                    + '* Strategies:\n'

                this.totalResults.forEach(res => {
                    this.params.description += '     #' + res.id + ' ' + res.name + ' (' + res.result + ')\n'
                })
            }

            this.$refs.saveGameResultsModalRef.show()
        }
    }
</script>

<style scoped>

</style>
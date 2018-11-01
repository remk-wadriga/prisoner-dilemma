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
            totalResults: Array,
            individualResults: Object,
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
            if (this.params.name === null) {
                const date = new Date();

                let month = date.getMonth()
                if (month < 10) {
                    month = '0' + month
                }
                let day = date.getDate()
                if (day < 10) {
                    day = '0' + day
                }

                this.params.name = 'Game ' + day + '-' + month + '-' + date.getFullYear() + ' '
                    + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds()
                    + ' (' + this.totalResults.length + ' strategies)'
            }
            this.$refs.saveGameResultsModalRef.show()
        }
    }
</script>

<style scoped>

</style>
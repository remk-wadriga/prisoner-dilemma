<template src="@/templates/game/game.html" />

<script>
    import GameParams from '@/components/game/GameParams'
    import GameResults from '@/components/game/GameResults'

    export default {
        name: "Game",
        components: { GameParams, GameResults },
        data() {
            return {
                gameResults: null
            }
        },
        methods: {
            setGameResults(data) {
                this.gameResults = data
            }
        },
        mounted() {

        }
    }
</script>

<style scoped>

</style>
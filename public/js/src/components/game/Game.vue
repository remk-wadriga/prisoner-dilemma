<template src="@/templates/game/game.html" />

<script>
    import GameParams from '@/components/game/GameParams'
    import GameResults from '@/components/game/GameResults'

    export default {
        name: "Game",
        components: { GameParams, GameResults },
        data() {
            return {
                game: null,
                gameResults: null
            }
        },
        methods: {
            setGameResults(data) {
                this.gameResults = data
            }
        },
        mounted() {
            console.log(this.gameResults)
        }
    }
</script>

<style scoped>

</style>
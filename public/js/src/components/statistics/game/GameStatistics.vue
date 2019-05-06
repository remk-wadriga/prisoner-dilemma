<template src="@/templates/statistics/game/game-statistics.html" />

<script>
    import Api from '@/helpers/Api.js'
    import GameStatisticsByStrategies from '@/components/statistics/game/GameStatisticsByStrategies'

    export default {
        name: "GameStatistics",
        components: { GameStatisticsByStrategies },
        data() {
            return {
                game: null,
                lazyLoad: true
            }
        },
        methods: {

        },
        mounted() {
            const id = this.$route.params.id

            Api.methods.request(['game_url', {id}], {}, 'GET', response => {
                this.game = response.info

                this.$store.commit('setContentTitle', 'Game "' + this.game.name + '" statistics')
                this.$store.commit('setBreadcrumbs', [
                    {title: 'Games', url: 'games_list'},
                    {title: this.game.name, url: {name: 'game_view', params: {id}}},
                    {title: 'Game statistics', url: {name: 'game_statistics', params: {id}}}
                ])
                this.$store.commit('setPageTopButtons', [])
            })
        }
    }
</script>

<style scoped>

</style>
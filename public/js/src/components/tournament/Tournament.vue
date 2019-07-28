<template src="@/templates/tournament/tournament.html" />

<script>
    import Api from '@/helpers/Api.js'
    import TournamentParams from '@/components/tournament/TournamentParams'

    export default {
        name: "Tournament",
        components: { TournamentParams },
        data() {
            return {
                tournament: null,
                tournamentParams: null
            }
        },
        mounted() {
            this.$store.commit('setPageTitle', '')
            this.$store.commit('setContentTitle', this.tournament !== null ? this.tournament.name : 'Start tournament')
            //this.$store.commit('setBreadcrumbs', [{title: 'Games', url: 'games_list'}, {title: 'Game', url: 'game_start'}])
            this.$store.commit('setPageTopButtons', [])


        }
    }
</script>

<style scoped>

</style>
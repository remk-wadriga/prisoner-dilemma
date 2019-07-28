<template src="@/templates/tournament/tournament-params.html" />

<script>
    import Api from '@/helpers/Api.js'

    export default {
        name: "TournamentParams",
        data() {
            return {
                isReady: false,
                strategies: [],
                params: {

                },
                checkedStrategiesIds: [],
                checkedStrategiesIdsOptions: []
            }
        },
        props: {
            tournament: Object,
            tournamentParams: Object
        },
        methods: {
            startTournament() {
                console.log('startTournament!')
            }
        },
        mounted() {
            this.strategies = this.$store.state.strategy.checked
            Api.methods.request('params_tournament_url', {}, 'GET', response => {
                //this.$store.commit('setGameParams', response)
                this.params = response
                this.isReady = true
            })
        }
    }
</script>

<style scoped>

</style>
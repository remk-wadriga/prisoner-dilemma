<template src="@/templates/statistics/total/total-statistics.html" />

<script>
    import TotalStatisticsByDates from '@/components/statistics/total/TotalStatisticsByDates'
    import TotalStatisticsByStrategies from '@/components/statistics/total/TotalStatisticsByStrategies'
    import TotalStatisticsByGames from '@/components/statistics/total/TotalStatisticsByGames'
    import TotalStatisticsByRoundsCount from '@/components/statistics/total/TotalStatisticsByRoundsCount'

    export default {
        name: "TotalStatistics",
        components: { TotalStatisticsByDates, TotalStatisticsByStrategies, TotalStatisticsByGames, TotalStatisticsByRoundsCount },
        data() {
            return {
                lazyLoad: true
            }
        },
        methods: {

        },
        mounted() {
            const id = this.$route.params.id

            this.$store.commit('setContentTitle', 'Total statistics')
            this.$store.commit('setBreadcrumbs', [
                {title: 'Home', url: 'app_homepage'},
                {title: 'Total statistics', url: {name: 'total_statistics', params: {id}}}
            ])
            this.$store.commit('setPageTopButtons', [])
        }
    }
</script>

<style scoped>

</style>
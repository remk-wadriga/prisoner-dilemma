<template src="@/templates/statistics/total/total-statistics.html" />

<script>
    import Api from '@/helpers/Api.js'
    import Formatter from '@/helpers/Formatter'
    import TotalStatisticsByDates from '@/components/statistics/total/TotalStatisticsByDates'
    import TotalStatisticsByStrategies from '@/components/statistics/total/TotalStatisticsByStrategies'
    import TotalStatisticsByGames from '@/components/statistics/total/TotalStatisticsByGames'
    import TotalStatisticsByRoundsCount from '@/components/statistics/total/TotalStatisticsByRoundsCount'
    import DateRangePicker from '@/components/DateRangePicker'
    import GameParamsFilter from '@/components/statistics/GameParamsFilter'

    export default {
        name: "TotalStatistics",
        components: {
            TotalStatisticsByDates,
            TotalStatisticsByStrategies,
            TotalStatisticsByGames,
            TotalStatisticsByRoundsCount,
            DateRangePicker,
            GameParamsFilter
        },
        data() {
            return {
                lazyLoad: true,
                isReady: false,
                selectedDates: {start: null, end: null},
                gameParamsFilters: null
            }
        },
        methods: {
            setDatesRange(range) {
                this.$store.commit('setStatistics', null)
                this.selectedDates = range
            },
            setGameParamsFilters(filters)
            {
                this.$store.commit('setStatistics', null)
                this.gameParamsFilters = {}
                Object.keys(filters).forEach(key => {
                    if (filters[key] !== null) {
                        this.gameParamsFilters['game_' + key] = filters[key]
                    }
                })
            }
        },
        mounted() {
            this.$store.commit('setContentTitle', 'Total statistics')
            this.$store.commit('setBreadcrumbs', [
                {title: 'Home', url: 'app_homepage'},
                {title: 'Total statistics', url: 'total_statistics'}
            ])
            this.$store.commit('setPageTopButtons', [])

            // Get dates range
            let statisticsDates = this.$store.state.statistics['statisticsDates']
            if (statisticsDates !== undefined) {
                this.selectedDates = statisticsDates
                this.isReady = true
            } else {
                Api.methods.request('params_statistics_dates_url', {}, 'GET', response => {
                    if (response) {
                        const selectedDates = {
                            start: Formatter.methods.formatDate(response.start),
                            end: Formatter.methods.formatDate(response.end)
                        }
                        this.$store.commit('setStatistics', {id: 'statisticsDates', data: selectedDates})
                        this.selectedDates = selectedDates
                    }
                    this.isReady = true
                })
            }
        }
    }
</script>

<style scoped>

</style>
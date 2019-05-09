<template src="@/templates/statistics/total/total-statistics-by-dates.html" />

<script>
    import Api from '@/helpers/Api.js'
    import LineChart from '@/components/charts/LineChart'

    export default {
        name: "TotalStatisticsByDates",
        components: { LineChart },
        data() {
            return {
                isReady: false,
                chartLabels: [],
                chartData: [],
                chartOptions: null,
                chartTooltipTitleCallback: null,
                chartTooltipLabelCallback: null
            }
        },
        props: {
            selectedDates: Object
        },
        watch: {
            selectedDates() {
                // Refresh page
                this.refreshData()
            }
        },
        methods: {
            init (statistics) {
                let bales = []
                let gamesCount = []
                let roundsCount = []
                this.chartLabels = []

                statistics.forEach(data => {
                    this.chartLabels.push(data.gameDate)

                    if (this.lastGameDate === null || data.gameDate > this.lastGameDate) {
                        this.lastGameDate = data.gameDate
                    }

                    bales.push(data.bales)
                    gamesCount.push(data.gamesCount)
                    roundsCount.push(data.roundsCount)
                })

                this.chartData = [
                    {
                        label: 'Bales',
                        data: bales
                    }
                ]

                this.chartTooltipLabelCallback = item => {
                    return 'Bales: ' + bales[item.index] + '; Games count: ' + gamesCount[item.index] + '; Rounds count: ' + roundsCount[item.index]
                }

                this.isReady = true
            },
            refreshData() {
                let params = {}
                if (this.selectedDates) {
                    params.fromDate = this.selectedDates.start
                    params.toDate = this.selectedDates.end
                }
                Api.methods.request(['total_statistics_by_dates_url', params], {}, 'GET', response => {
                    this.$store.commit('setStatistics', {id: 'totalStatisticsByDates', data: response})
                    this.init(response)
                })
            }
        },
        mounted() {
            let statistics = this.$store.state.statistics['totalStatisticsByDates']
            if (statistics) {
                this.init(statistics)
            } else {
                this.refreshData()
            }
        }
    }
</script>

<style scoped>

</style>
<template src="@/templates/statistics/total/total-statistics-by-rounds-count.html" />

<script>
    import Api from '@/helpers/Api.js'
    import LineChart from '@/components/charts/LineChart'

    export default {
        name: "TotalStatisticsByRoundsCount",
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
                this.refreshData()
            }
        },
        methods: {
            init (statistics) {
                let bales = []
                let gamesCount = []
                this.chartLabels = []

                statistics.forEach(data => {
                    this.chartLabels.push(data.roundsCount)
                    bales.push(data.bales)
                    gamesCount.push(data.gamesCount)
                })

                this.chartData = [
                    {
                        label: 'Bales',
                        data: bales
                    }
                ]

                this.chartTooltipTitleCallback = item => {
                    return 'Rounds: ' + this.chartLabels[item[0].index]
                }
                this.chartTooltipLabelCallback = item => {
                    return 'Bales: ' + bales[item.index] + '; Games count: ' + gamesCount[item.index]
                }

                this.isReady = true
            },
            refreshData() {
                let params = {}
                if (this.selectedDates) {
                    params.fromDate = this.selectedDates.start
                    params.toDate = this.selectedDates.end
                }
                Api.methods.request(['total_statistics_by_rounds_count_url', params], {}, 'GET', response => {
                    this.$store.commit('setStatistics', {id: 'totalStatisticsByRoundsCount', data: response})
                    this.init(response)
                })
            }
        },
        mounted() {
            let statistics = this.$store.state.statistics['totalStatisticsByRoundsCount']
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
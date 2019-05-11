<template src="@/templates/statistics/total/total-statistics-by-strategies.html" />

<script>
    import Api from '@/helpers/Api.js'
    import BarChart from '@/components/charts/BarChart'

    export default {
        name: "TotalStatisticsByStrategies",
        components: { BarChart },
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
            selectedDates: Object,
            gameParamsFilters: Object
        },
        watch: {
            selectedDates() {
                this.refreshData()
            },
            gameParamsFilters() {
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
                    this.chartLabels.push(data.strategy)
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

                this.chartTooltipTitleCallback = item => {
                    return 'Strategy: ' + this.chartLabels[item[0].index]
                }
                this.chartTooltipLabelCallback = item => {
                    return 'Bales: ' + bales[item.index] + '; Games count: ' + gamesCount[item.index] + '; Rounds count: ' + roundsCount[item.index]
                }

                this.isReady = true
            },
            refreshData() {
                let params = this.gameParamsFilters ? this.gameParamsFilters : {}
                if (this.selectedDates) {
                    params.fromDate = this.selectedDates.start
                    params.toDate = this.selectedDates.end
                }

                Api.methods.request(['total_statistics_by_strategies_url', params], {}, 'GET', response => {
                    this.$store.commit('setStatistics', {id: 'totalStatisticsByStrategies', data: response})
                    this.init(response)
                })
            }
        },
        mounted() {
            let statistics = this.$store.state.statistics['totalStatisticsByStrategies']
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
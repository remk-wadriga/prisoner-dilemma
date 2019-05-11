<template src="@/templates/statistics/game-params-filter.html" />

<script>
    import Api from '@/helpers/Api.js'

    export default {
        name: "GameParamsFilter",
        props: {
            selectedDates: {type: Object, default() { return {start: null, end: null} }}
        },
        data() {
            return {
                isReady: false,
                params: {},
                selectedParams: {
                    roundsCount: null,
                    balesForWin: null,
                    balesForLoos: null,
                    balesForCooperation: null,
                    balesForDraw: null
                }
            }
        },
        methods: {
            setRoundsCount(val) {
                this.selectedParams.roundsCount = val
                this.$emit('setGameParamsFilters', this.selectedParams)
                this.refresh()
            },
            setBalesForWin(val) {
                this.selectedParams.balesForWin = val
                this.$emit('setGameParamsFilters', this.selectedParams)
                this.refresh()
            },
            setBalesForLoos(val) {
                this.selectedParams.balesForLoos = val
                this.$emit('setGameParamsFilters', this.selectedParams)
                this.refresh()
            },
            setBalesForCooperation(val) {
                this.selectedParams.balesForCooperation = val
                this.$emit('setGameParamsFilters', this.selectedParams)
                this.refresh()
            },
            setBalesForDraw(val) {
                this.selectedParams.balesForDraw = val
                this.$emit('setGameParamsFilters', this.selectedParams)
                this.refresh()
            },
            refresh() {
                this.isReady = false
                this.$store.commit('setStatistics', {id: 'gamesFilterParams', data: undefined})

                let params = {
                    fromDate: this.selectedDates.start,
                    toDate: this.selectedDates.end
                }
                Object.keys(this.selectedParams).forEach(key => {
                    params['game_' + key] = this.selectedParams[key]
                })

                Api.methods.request(['params_game_filters', params], {}, 'GET', response => {
                    this.params = response
                    this.params.roundsCount
                        .sort((a, b) => { return a - b })
                        .unshift({text: '---', value: null})
                    this.params.balesForWin
                        .sort((a, b) => { return a - b })
                        .unshift({text: '---', value: null})
                    this.params.balesForLoos
                        .sort((a, b) => { return a - b })
                        .unshift({text: '---', value: null})
                    this.params.balesForCooperation
                        .sort((a, b) => { return a - b })
                        .unshift({text: '---', value: null})
                    this.params.balesForDraw
                        .sort((a, b) => { return a - b })
                        .unshift({text: '---', value: null})
                    this.$store.commit('setStatistics', {id: 'gamesFilterParams', data: response})
                    this.isReady = true
                })
            }
        },
        mounted() {
            this.params = this.$store.state.statistics['gamesFilterParams']
            if (!this.params) {
                this.refresh()
            } else {
                this.isReady = true
            }
        }
    }
</script>

<style scoped>

</style>
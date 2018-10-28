<template src="@/templates/game/game-results.html" />

<script>
    export default {
        name: "GameResults",
        data() {
            return {
                sum: 0,
                score: [],
                hasIndividualResults: false,
                strategies: {},
                winner: null,
                looser: null,
                fields: {
                    id: {
                        sortable: true
                    },
                    name: {
                        sortable: true
                    },
                    result: {
                        sortable: true
                    },
                    actions: {
                        label: 'Individual results'
                    }
                },
                individualResultFields: {
                    partnerID: {
                        label: 'Id',
                        sortable: true
                    },
                    partnerName: {
                        label: 'Name',
                        sortable: true
                    },
                    result: {
                        label: 'Res',
                        sortable: true
                    },
                    partnerResult: {
                        label: 'Partner res',
                        sortable: true
                    }
                },
                individualResult: [],
                individualResults: {},
                individualResultsStrategy: null
            }
        },
        props: {
            results: Object
        },
        methods: {
            showIndividualResults (strategy) {
                if (this.individualResults[strategy.id] !== undefined) {
                    this.individualResultsStrategy = strategy
                    this.individualResult = this.individualResults[strategy.id]
                } else {
                    this.individualResultsStrategy = null
                }
            }
        },
        mounted() {
            if (this.results != null && this.results.results !== undefined) {
                if (this.results.results.sum !== undefined) {
                    this.sum = this.results.results.sum
                }
                if (this.results.results.total !== undefined) {
                    this.score = this.results.results.total
                    this.score
                        .sort((one, due) => one.result < due.result ? 1 : -1)
                        .forEach(res => {
                            if (this.winner === null || this.winner.result < res.result) {
                                this.winner = res
                            }
                            if (this.looser === null || this.looser.result > res.result) {
                                this.looser = res
                            }
                            this.strategies[res.id] = res
                        })
                }
                if (this.results.results.individual !== undefined) {
                    Object.keys(this.results.results.individual).forEach(id => {
                        this.individualResults[id] = []
                        Object.keys(this.results.results.individual[id]).forEach(partnerID => {
                            this.individualResults[id].push(this.results.results.individual[id][partnerID])
                        })
                    })
                    Object.keys(this.individualResults).forEach(id => {
                        this.individualResults[id].sort((one, due) => one.result < due.result ? 1 : -1)
                    })
                    this.hasIndividualResults = Object.keys(this.individualResults).length > 0
                }
            }
        }
    }
</script>

<style scoped>

</style>
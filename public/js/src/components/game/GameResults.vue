<template src="@/templates/game/game-results.html" />

<script>
    export default {
        name: "GameResults",
        data() {
            return {
                sum: 0,
                score: [],
                hasCouplesResults: false,
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
                    id: {
                        sortable: true
                    },
                    name: {
                        sortable: true
                    },
                    strategyResult: {
                        label: 'Res',
                        sortable: true
                    },
                    partnerResult: {
                        label: 'Partner res',
                        sortable: true
                    }
                },
                individualResults: {},
                individualResult: [],
                individualResultsStrategy: null
            }
        },
        props: {
            results: Object
        },
        methods: {
            showIndividualResults (strategy) {
                this.individualResultsStrategy = strategy
                let id = strategy.id

                if (this.individualResults[id] !== undefined) {
                    this.individualResult = this.individualResults[id]
                    return
                }
                if (!this.hasCouplesResults || this.strategies[id] === undefined) {
                    return
                }

                this.individualResults[id] = []

                Object.keys(this.results.results.couples).forEach(key => {
                    if (!key.split(':').includes(id.toString())) {
                        return
                    }

                    let coupleResult = this.results.results.couples[key]
                    let strategy, partner = null
                    Object.keys(coupleResult).forEach(index => {
                        if (index == id) {
                            if (this.strategies[index] === undefined) {
                                return
                            }
                            strategy = {
                                id: this.strategies[index].id,
                                name: this.strategies[index].name,
                                result: coupleResult[index]
                            }
                        } else {
                            if (this.strategies[index] === undefined) {
                                return
                            }
                            partner = {
                                id: this.strategies[index].id,
                                name: this.strategies[index].name,
                                result: coupleResult[index]
                            }
                        }
                    })

                    this.individualResults[id].push({
                        id: partner.id,
                        name: partner.name,
                        strategyResult: strategy.result,
                        partnerResult: partner.result
                    })
                })

                this.individualResults[id].sort((one, due) => one.strategyResult < due.strategyResult ? 1 : 0)
                this.individualResult = this.individualResults[id]
            }
        },
        mounted() {
            if (this.results != null && this.results.results !== undefined) {
                if (this.results.results.sum !== undefined) {
                    this.sum = this.results.results.sum
                }
                if (this.results.results.total !== undefined) {
                    this.score = this.results.results.total
                    this.score.sort((one, due) => one.result < due.result ? 1 : 0)
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
                if (this.results.results.couples !== undefined) {
                    this.hasCouplesResults = Object.keys(this.results.results.couples).length > 0
                }
            }
        }
    }
</script>

<style scoped>

</style>
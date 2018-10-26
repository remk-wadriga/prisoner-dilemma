<template src="@/templates/game/game-results.html" />

<script>
    export default {
        name: "GameResults",
        data() {
            return {
                sum: 0,
                score: [],
                couples: [],
                winner: null,
                looser: null
            }
        },
        props: {
            results: Object
        },
        methods: {

        },
        mounted() {
            if (this.results != null && this.results.results !== undefined) {
                if (this.results.results.sum !== undefined) {
                    this.sum = this.results.results.sum
                }
                if (this.results.results.total !== undefined) {
                    this.score = this.results.results.total
                    this.score.sort((one, due) => { return one.result < due.result ? 1 : 0 })
                }
                if (this.results.results.couples !== undefined) {
                    let tmpCoupleResIds = []
                    this.score.forEach(res => {
                        if (this.winner === null || this.winner.result < res.result) {
                            this.winner = res
                        }
                        if (this.looser === null || this.looser.result > res.result) {
                            this.looser = res
                        }

                        for (let index in this.results.results.couples) {
                            if (!tmpCoupleResIds.includes(index)) {
                                tmpCoupleResIds.push(index)

                                let coupleRes = this.results.results.couples[index]
                                let tmpCoupleRes = []

                                index.split(':').forEach((id) => {
                                    if (coupleRes[id] !== undefined) {
                                        tmpCoupleRes.push({
                                            id: id,
                                            name: res.name,
                                            result: coupleRes[id]
                                        })
                                    }
                                })

                                if (tmpCoupleRes.length === 2) {
                                    this.couples.push(tmpCoupleRes)
                                }
                            }
                        }
                    })
                }
            }
        }
    }
</script>

<style scoped>

</style>
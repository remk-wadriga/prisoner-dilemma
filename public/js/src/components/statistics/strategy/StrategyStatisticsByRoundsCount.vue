<template src="@/templates/statistics/strategy/strategy-statistics-by-rounds-count.html" />

<script>
    export default {
        name: "StrategyStatisticsByRoundsCount",
        props: {
            strategy: Object
        },
        data() {
            return {

            }
        },
        methods: {

        },
        mounted() {
            console.log(this.strategy)
        }
    }
</script>

<style scoped>

</style>
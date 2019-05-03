<template src="@/templates/statistics/strategy/strategy-statistics-by-dates.html" />

<script>
    export default {
        name: "StrategyStatisticsByDates",
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
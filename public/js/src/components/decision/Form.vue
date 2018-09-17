<template src="@/templates/decision/form.html" />

<script>
    export default {
        name: "Form",
        computed: {
            decisions() {
                return this.$store.state.strategy.decisions
            }
        }
    }
</script>

<style scoped>

</style>
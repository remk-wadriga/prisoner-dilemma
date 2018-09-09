<template src="@/templates/index/logger.html" />

<script>
    export default {
        name: "Logger",
        methods: {
            closeMessage (event) {
                this.$store.commit('deleteLogMessage', event.target.getAttribute('data-id'))
            }
        },
        computed: {
            messages() {
                return this.$store.state.logger.messages
            }
        }
    }
</script>

<style scoped>

</style>
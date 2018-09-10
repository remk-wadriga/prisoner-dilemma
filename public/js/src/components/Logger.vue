<template src="@/templates/logger.html" />

<script>
    export default {
        name: "Logger",
        methods: {
            closeMessage (id) {
                this.$store.commit('deleteLogMessage', id)
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
<template src="@/templates/debugger.html" />

<script>
    export default {
        name: "Debugger",
        methods: {
            closeMessage (id) {
                this.$store.commit('deleteDebugMessage', id)
            }
        },
        computed: {
            messages() {
                return this.$store.state.debugger.messages
            },
            lastMessage() {
                let length = this.messages.length
                return this.messages[length - 1]
            }
        }
    }
</script>

<style scoped>

</style>
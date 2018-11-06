<template src="@/templates/game/delete-modal.html" />

<script>
    export default {
        name: "DeleteModal",
        data() {
            return {

            }
        },
        props: ['game', 'onCloseCallbackFunction'],
        methods: {
            deleteGame() {
                this.onCloseCallbackFunction()
            },
            close() {
                this.$refs.deleteGameModalRef.hide()
                this.$parent.deleteGameVisible = false
            }
        },
        mounted() {
            this.$refs.deleteGameModalRef.show()
        }
    }
</script>

<style scoped>

</style>
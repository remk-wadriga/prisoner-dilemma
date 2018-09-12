<template src="@/templates/left-menu.html" />

<script>
    export default {
        name: "LeftMenu",
        computed: {
            leftMenuItems() {
                return this.$store.state.app.leftMenuItems
            }
        }
    }
</script>

<style scoped>

</style>
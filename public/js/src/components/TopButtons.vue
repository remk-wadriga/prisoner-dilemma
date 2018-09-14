<template<template src="@/templates/top-buttons.html" />

<script>
    export default {
        name: "TopButtons",
        data() {
            return {
                btnSize: 'lg'
            }
        },
        computed: {
            buttons() {
                //  { variant: 'primary', caption: 'Toggle 1', state: true }
                return this.$store.state.app.pageTopButtons
            }
        }
    }
</script>

<style scoped>

</style>
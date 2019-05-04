<template src="@/templates/content.html" />

<script>
    import TopButtons from '@/components/TopButtons'

    export default {
        name: "Content",
        components: { TopButtons },
        computed: {
            pageTitle() {
                return this.$store.state.app.contentTitle
            }
        }
    }
</script>

<style scoped>

</style>
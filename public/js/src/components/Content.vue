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
    .min-width-eighty-presents {
        min-width: 80%;
    }
    .min-width-seventy-presents {
        min-width: 70%;
    }
</style>
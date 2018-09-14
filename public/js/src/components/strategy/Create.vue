<template src="@/templates/strategy/create.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "Create",
        methods: {

        },
        mounted() {
            this.$store.commit('setContentTitle', 'Create strategy')
            this.$store.commit('setBreadcrumbs', [{title: 'Strategies', url: 'app_homepage'}, {title: 'Create', url: 'strategy_create'}])
        }
    }
</script>

<style scoped>

</style>
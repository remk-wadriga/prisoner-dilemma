<!--<template src="@/templates/strategy/create.html" />-->

<script>
    import Form from '@/components/strategy/Form'

    export default {
        name: "StrategyCreate",
        components: { Form },
        template: '<Form/>',
        data() {
            return {

            }
        },
        methods: {

        },
        mounted() {
            this.$store.commit('setContentTitle', 'Create strategy')
            this.$store.commit('setBreadcrumbs', [
                {title: 'Strategies', url: 'app_homepage'},
                {title: 'Create', url: 'strategy_create'},
            ])
            this.$store.commit('setPageTopButtons', [])
        }
    }
</script>

<style scoped>

</style>
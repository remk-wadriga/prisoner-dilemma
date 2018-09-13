<template src="@/templates/strategy/view.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "View",
        data() {
            return {
                strategy: null
            }
        },
        methods: {

        },
        mounted() {
            Api.methods.request(['strategy_view', {id: this.$route.params.id}], {}, 'GET', response => {
                this.strategy = response
                this.$store.commit('setPageTitle', 'Strategy "' + this.strategy.name + '"')
            })
        }
    }
</script>

<style scoped>

</style>
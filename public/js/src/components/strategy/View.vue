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
            deleteStrategy() {
                console.log('Delete')
            }
        },
        mounted() {
            const id = this.$route.params.id
            Api.methods.request(['strategy_url', {id}], {}, 'GET', response => {
                this.strategy = response
                this.$store.commit('setContentTitle', 'Strategy "' + this.strategy.name + '"')
                this.$store.commit('setPageTopButtons', [
                    {title: 'Update', url: ['strategy_update', {id}], type: 'primary'},
                    {title: 'Delete', url: '#', type: 'danger', click: 'deleteStrategy'}
                ])
                this.$store.commit('setBreadcrumbs', [
                    {title: 'Strategies', url: 'app_homepage'},
                    {title: this.strategy.name, url: {name: 'strategy_view', params: {id}}}
                ])
            })
        }
    }
</script>

<style scoped>

</style>
<template src="@/templates/strategy/list.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "List",
        data() {
            return {
                strategies: [],
                fields: ['name', 'description', 'status', 'actions']
            }
        },
        methods: {
            selectStrategy(id) {
                this.$router.push({name: 'strategy_view', params: {id: id}})
            },
            updateStrategy(id) {
                this.$router.push({name: 'strategy_update', params: {id: id}})
            },
            deleteStrategy(id) {

            }
        },
        mounted() {
            Api.methods.request('app_homepage', {}, 'GET', response => {
                this.strategies = response
                this.$store.commit('setContentTitle', 'Strategies')
                this.$store.commit('setBreadcrumbs', [{title: 'Strategies', url: 'app_homepage'}])
                this.$store.commit('setPageTopButtons', [{title: 'Create new strategy', url: 'strategy_create_url', type: 'success'}])
            })
        }
    }
</script>

<style scoped>

</style>
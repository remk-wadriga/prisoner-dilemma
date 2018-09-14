<template src="@/templates/strategy/list.html" />

<script>
    import Api from '@/helpers/Api'
    import DeleteStrategy from '@/components/strategy/Delete.vue'

    export default {
        name: "List",
        components: { DeleteStrategy },
        data() {
            return {
                strategies: [],
                fields: ['name', 'description', 'status', 'actions'],
                deleteStrategyVisible: false,
                viewBtnVar: 'primary',
                updateBtnVar: 'success',
                deleteBtnVar: 'danger',
            }
        },
        methods: {
            selectStrategy(id) {
                this.$router.push({name: 'strategy_view', params: {id: id}})
            },
            updateStrategy(id) {
                this.$router.push({name: 'strategy_update', params: {id: id}})
            },
            openDeleteStrategyModal(id) {
                this.$store.commit('selectStrategy', id)
                this.deleteStrategyVisible = true
            }
        },
        mounted() {
            Api.methods.request('app_homepage', {}, 'GET', response => {
                this.strategies = response
                this.$store.commit('setContentTitle', 'Strategies')
                this.$store.commit('setBreadcrumbs', [{title: 'Strategies', url: 'app_homepage'}])
                this.$store.commit('setPageTopButtons', [{title: 'Create new strategy', type: 'success', click: {url: {name: 'strategy_create'}}}])
            })
        }
    }
</script>

<style scoped>

</style>
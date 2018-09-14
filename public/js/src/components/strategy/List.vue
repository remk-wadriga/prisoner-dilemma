<template src="@/templates/strategy/list.html" />

<script>
    import Api from '@/helpers/Api'
    import DeleteStrategy from '@/components/strategy/Delete.vue'

    export default {
        name: "StrategyList",
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
            selectedStrategy(id) {
                this.$router.push({name: 'strategy_view', params: {id: id}})
            },
            updateStrategy(strategy) {
                this.$store.commit('selectedStrategy', strategy)
                this.$router.push({name: 'strategy_update', params: {id: strategy.id}})
            },
            openDeleteStrategyModal(strategy) {
                this.$store.commit('setCloseModalCallback', () => {
                    Api.methods.request('app_homepage', {}, 'GET', response => {
                        this.strategies = response
                        this.$store.commit('setCloseModalCallback', null)
                    })
                })
                this.$store.commit('selectedStrategy', strategy)
                this.deleteStrategyVisible = true
            }
        },
        mounted() {
            Api.methods.request('app_homepage', {}, 'GET', response => {
                this.strategies = response
                this.$store.commit('setPageTitle', '')
                this.$store.commit('setContentTitle', 'Strategies')
                this.$store.commit('setBreadcrumbs', [{title: 'Strategies', url: 'app_homepage'}])
                this.$store.commit('setPageTopButtons', [{title: 'Create new strategy', type: 'success', click: {url: {name: 'strategy_create'}}}])
                this.$store.commit('selectedStrategy', null)
            })
        }
    }
</script>

<style scoped>

</style>
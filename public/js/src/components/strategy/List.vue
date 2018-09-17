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
                fields: [
                    {
                        key: 'name',
                        sortable: true
                    },
                    {
                        key: 'status',
                        sortable: true
                    },
                    {
                        key: 'shortDescription',
                        label: 'Description',
                        sortable: false
                    },
                    'actions',
                ],
                deleteStrategyVisible: false,
                viewBtnVar: 'primary',
                updateBtnVar: 'success',
                deleteBtnVar: 'danger',
                filter: '',
                status: '',
                currentPage: 1,
                perPage: 10,
                totalRows: 0,
            }
        },
        methods: {
            selectedStrategy(id) {
                this.$router.push({name: 'strategy_view', params: {id: id}})
            },
            updateStrategy(strategy) {
                this.$store.commit('selectedStrategyId', strategy.id)
                this.$router.push({name: 'strategy_update', params: {id: strategy.id}})
            },
            openDeleteStrategyModal(strategy) {
                this.$store.commit('setCloseModalCallback', () => {
                    Api.methods.request('app_homepage', {}, 'GET', response => {
                        this.strategies = response
                        this.$store.commit('setCloseModalCallback', null)
                    })
                })
                this.$store.commit('selectedStrategyId', strategy.id)
                this.deleteStrategyVisible = true
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length
                this.currentPage = 1
            }
        },
        mounted() {
            Api.methods.request('app_homepage', {}, 'GET', response => {
                response.forEach(item => {
                    item.shortDescription = item.description.substring(0, 150) + '...'
                    this.totalRows++
                })
                this.strategies = response
                this.$store.commit('setPageTitle', '')
                this.$store.commit('setContentTitle', 'Strategies')
                this.$store.commit('setBreadcrumbs', [{title: 'Strategies', url: 'app_homepage'}])
                this.$store.commit('setPageTopButtons', [{title: 'Create new strategy', type: 'success', click: {url: {name: 'strategy_create'}}}])
                this.$store.commit('selectedStrategyId', null)
                this.$store.commit('setStrategyDecisions', [])
            })
        }
    }
</script>

<style scoped>

</style>
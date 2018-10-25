<template src="@/templates/strategy/list.html" />

<script>
    import Vue from 'vue'
    import Api from '@/helpers/Api'
    import DeleteStrategy from '@/components/strategy/Delete.vue'

    export default {
        name: "StrategyList",
        components: { DeleteStrategy },
        data() {
            return {
                strategies: [],
                fields: {
                    checkboxes: {
                    },
                    name: {
                        sortable: true
                    },
                    status: {
                        sortable: true
                    },
                    shortDescription: {
                        label: 'Description',
                        sortable: false
                    },
                    actions: {
                        label: 'Actions'
                    },
                },
                deleteStrategyVisible: false,
                viewBtnVar: 'primary',
                updateBtnVar: 'success',
                deleteBtnVar: 'danger',
                filter: '',
                status: '',
                currentPage: 1,
                perPage: 10,
                totalRows: 0,
                checkedStrategiesIds: {},
                allStrategiesSelected: false,
                checkedStrategiesIdsArray: []
            }
        },
        watch: {
            allStrategiesSelected() {
                this.strategies.forEach(item => {
                    this.checkedStrategiesIds[item.id] = this.allStrategiesSelected
                })
            },
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
                // Set current strategy ID
                this.$store.commit('selectedStrategyId', strategy.id)
                // Set popup "onClose" callback function
                DeleteStrategy.computed.onCloseCallback = () => {
                    Api.methods.request('app_homepage', {}, 'GET', response => {
                        // View new strategies list
                        this.strategies = response
                        // Now delete popup is not visible
                        this.deleteStrategyVisible = false
                    })
                }
                // Now delete popup is visible
                this.deleteStrategyVisible = true
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length
                this.currentPage = 1
            },
            startGame() {
                this.checkedStrategiesIdsArray = []
                this.strategies.forEach(strategy => {
                    if (this.checkedStrategiesIds[strategy.id] === true) {
                        this.checkedStrategiesIdsArray.push(strategy.id)
                    }
                })
                console.log(this.checkedStrategiesIdsArray)
            }
        },
        mounted() {
            Api.methods.request('app_homepage', {}, 'GET', response => {
                response.forEach(item => {
                    this.checkedStrategiesIds[item.id] = false
                    item.shortDescription = item.description.substring(0, 150) + '...'
                    this.totalRows++
                })
                this.strategies = response
                this.$store.commit('setPageTitle', '')
                this.$store.commit('setContentTitle', 'Strategies')
                this.$store.commit('setBreadcrumbs', [{title: 'Strategies', url: 'app_homepage'}])
                this.$store.commit('setPageTopButtons', [{title: 'Create new strategy', type: 'success', click: {url: {name: 'strategy_create'}}}])
                this.$store.commit('selectedStrategyId', null)
            })
        }
    }
</script>

<style scoped>

</style>
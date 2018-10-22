<template src="@/templates/strategy/form.html" />

<script>
    import Api from '@/helpers/Api'
    import DecisionForm from '@/components/decision/Form'
    import GenerateRandomStrategy from '@/components/strategy/GenerateRandom'

    export default {
        name: "StrategyForm",
        components: { DecisionForm, GenerateRandomStrategy },
        data() {
            return {
                id: null,
                name: null,
                description: null,
                status: 'enabled',
                decisionsData: null,
                isNewRecord: true,
                isMounted: false,
                generateStrategyVisible: false
            }
        },
        methods: {
            setParams(strategy) {
                this.isNewRecord = false
                this.id = strategy.id
                this.name = strategy.name
                this.description = strategy.description
                this.status = strategy.status
                this.decisionsData = strategy.decisionsData
                this.$store.commit('selectedStrategyId', null)
            },
            changeDecisionsData(data) {
                this.decisionsData = data
            },
            openGenerateRandomStrategyModal() {
                // Set popup "onClose" callback function
                GenerateRandomStrategy.computed.onCloseCallback = () => {
                    this.generateStrategyVisible = false
                    const id = this.$store.state.strategy.selectedId
                    if (id) {
                        this.$router.replace({name: 'strategy_update', params: {id}})
                        this.$router.go(0);
                    }
                }
                // Now delete popup is visible
                this.generateStrategyVisible = true
            },
            submitStrategyFrom() {
                const data = {
                    strategy_form: {
                        name: this.name,
                        description: this.description,
                        status: this.status,
                        decisionsData: this.decisionsData,
                    }
                }
                let method = '';
                let url = '';
                if (this.isNewRecord) {
                    method = 'POST'
                    url = 'strategy_create_url'
                } else {
                    method = 'PUT'
                    url = ['strategy_url', {id: this.id}]
                }

                console.log(this.decisionsData)

                /*Api.methods.request(url, data, method, response => {
                    this.$router.push({name: 'strategy_view', params: {id: response.id}})
                })*/
            }
        },
        created() {
            let id = this.$route.params.id
            if (!id) {
                id = this.$store.state.strategy.selectedId
            }
            if (id !== null && id !== undefined && this.isMounted === false) {
                Api.methods.request(['strategy_url', {id}], {}, 'GET', response => {
                    this.setParams(response)
                    this.isMounted = true
                })
            } else if (this.isNewRecord) {
                this.decisionsData = {}
                this.isMounted = true
            }
        }
    }
</script>

<style scoped>

</style>
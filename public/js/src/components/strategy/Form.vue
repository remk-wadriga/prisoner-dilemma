<template src="@/templates/strategy/form.html" />

<script>
    import Api from '@/helpers/Api'
    import DecisionForm from '@/components/decision/Form'

    export default {
        name: "StrategyForm",
        components: { DecisionForm },
        data() {
            return {
                id: null,
                name: null,
                description: null,
                status: 'enabled',
                decisions: null,
                decisionsJson: '',
                isNewRecord: true,
                isMounted: false,
            }
        },
        methods: {
            submitStrategyFrom() {
                const data = {
                    strategy_form: {
                        name: this.name,
                        description: this.description,
                        status: this.status,
                        decisionsJson: this.decisionsJson !== '' ? this.decisionsJson : null,
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
                Api.methods.request(url, data, method, response => {
                    this.$router.push({name: 'strategy_view', params: {id: response.id}})
                })
            },
            setParams(strategy) {
                this.isNewRecord = false
                this.id = strategy.id
                this.name = strategy.name
                this.description = strategy.description
                this.status = strategy.status
                this.decisions = strategy.decisions
                this.$store.commit('selectedStrategyId', null)
                this.$store.commit('setStrategyDecisions', this.decisions)
            },
            clickParamsTab() {
                let decisionsModel = this.$store.state.strategy.decisionsFromModel
                if (decisionsModel !== null && decisionsModel !== undefined) {
                    this.decisionsJson = decisionsModel.serialize()
                }
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
                this.isMounted = true
            }
        }
    }
</script>

<style scoped>

</style>
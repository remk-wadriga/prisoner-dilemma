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
                decisions: [],
                isNewRecord: true,
            }
        },
        methods: {
            submitStrategyFrom() {
                const data = {
                    strategy_form: {
                        name: this.name,
                        description: this.description,
                        status: this.status
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
            }
        },
        mounted() {
            let id = this.$route.params.id
            if (!id) {
                id = this.$store.state.strategy.selectedId
            }
            if (id !== null && id !== undefined) {
                Api.methods.request(['strategy_url', {id}], {}, 'GET', response => {
                    this.setParams(response)
                })
            }
        }
    }
</script>

<style scoped>

</style>
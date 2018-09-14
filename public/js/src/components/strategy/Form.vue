<template src="@/templates/strategy/form.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "StrategyForm",
        data() {
            return {
                id: null,
                name: null,
                description: null,
                status: 'enabled',
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
                this.$store.commit('selectedStrategy', null)
            }
        },
        mounted() {
            let strategy = this.$store.state.strategy.selected
            const id = this.$route.params.id
            if (strategy === null && id !== undefined) {
                Api.methods.request(['strategy_url', {id}], {}, 'GET', response => {
                    this.setParams(response)
                })
            } else if (strategy !== null) {
                this.setParams(strategy)
            }
        }
    }
</script>

<style scoped>

</style>
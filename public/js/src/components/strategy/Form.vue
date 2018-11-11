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
        props: {
            strategy: Object
        },
        methods: {
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
                        decisionsData: this.$refs.decisionForm.getDecisionsData(),
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
                    if (this.isNewRecord) {
                        this.$router.replace({name: 'strategy_update', params: {id: response.id}})
                    }
                    this.$router.go(0);
                })

                return false
            }
        },
        mounted() {
            if (this.strategy) {
                this.isNewRecord = false

                this.id = this.strategy.id
                this.name = this.strategy.name
                this.description = this.strategy.description
                this.status = this.strategy.status
                this.decisionsData = this.strategy.decisionsData ? this.strategy.decisionsData : {}

                this.isMounted = true
            } else {
                this.isNewRecord = true
                this.decisionsData = {}
                this.isMounted = true
            }
        }
    }
</script>

<style scoped>

</style>
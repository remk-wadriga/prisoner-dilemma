<template src="@/templates/strategy/generate-random.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "GenerateRandom",
        data() {
            return {
                onCloseCallbackFunction: () => {  },
                name: null,
                steps: 0,
                params: {
                    acceptDecisionChance: 50,
                    chanceOfExtendingBranch: 70,
                    copyDecisionChance: 30,
                    randomDecisionChance: 20,
                }
            }
        },
        computed: {
            onCloseCallback: {
                get() {
                    return this.onCloseCallbackFunction
                },
                set(callback) {
                    this.onCloseCallbackFunction = callback
                }
            }
        },
        methods: {
            generateStrategy() {
                this.params.name = this.name
                this.params.steps = this.steps

                Api.methods.request('create_random_strategy_url', this.params, 'POST', response => {
                    this.$store.commit('selectedStrategyId', response.id)
                    this.onCloseCallback
                })
            },
            close() {
                this.$refs.generateStrategyModalRef.hide()
                this.$parent.generateStrategyVisible = false
            }
        },
        mounted() {
            Api.methods.request('params_strategy_url', {}, 'GET', response => {
                this.params = response
            })

            this.$refs.generateStrategyModalRef.show()
        }
    }
</script>

<style scoped>

</style>
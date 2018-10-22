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
                extendingChance: 70
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
                const data = {
                    name: this.name,
                    steps: this.steps,
                    extendingChance: this.extendingChance
                }
                Api.methods.request('create_random_strategy_url', data, 'POST', response => {
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
            this.$refs.generateStrategyModalRef.show()
        }
    }
</script>

<style scoped>

</style>
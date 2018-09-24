<template src="@/templates/strategy/delete.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "StrategyDelete",
        data() {
            return {
                onCloseCallbackFunction: () => {  },
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
            deleteStrategy() {
                let id = this.$route.params.id
                if (id === undefined) {
                    id = this.$store.state.strategy.selectedId
                }
                Api.methods.request(['strategy_url', {id}], {}, 'DELETE', () => {
                    // Unset selected strategy ID
                    this.$store.commit('selectedStrategyId', null)
                    // Call "onClose delete popup" callback function
                    this.onCloseCallback
                })
            },
            close() {
                this.$refs.deleteStrategyModalRef.hide()
                this.$parent.deleteStrategyVisible = false
            }
        },
        mounted() {
            this.$refs.deleteStrategyModalRef.show()
        }
    }
</script>

<style scoped>

</style>
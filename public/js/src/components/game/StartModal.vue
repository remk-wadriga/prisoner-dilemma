<template src="@/templates/game/start-modal.html" />

<script>
    export default {
        name: "StartGameModal",
        data() {
            return {
                onStartCallbackFunction: () => {  },
                checkedStrategiesIds: [],
                checkedStrategiesIdsOptions: []
            }
        },
        props: ['strategies'],
        computed: {
            onStartCallback: {
                get() {
                    return this.onStartCallbackFunction
                },
                set(callback) {
                    this.onStartCallbackFunction = callback
                }
            }
        },
        methods: {
            startGame() {
                this.onStartCallback
            },
            close() {
                this.$refs.startGameRef.hide()
                this.$parent.startGameVisible = false
            }
        },
        watch: {
            checkedStrategiesIds: {
                handler: function() {
                    if (this.checkedStrategiesIds.length != this.strategies.length) {
                        let checkedStrategies = []
                        this.strategies.forEach(strategy => {
                            if (this.checkedStrategiesIds.includes(strategy.id)) {
                                checkedStrategies.push(strategy)
                            }
                        })
                        this.$emit('changeCheckedStrategies', checkedStrategies)
                    }
                },
                deep: true
            }
        },
        mounted() {
            this.$refs.startGameRef.show()
            this.strategies.forEach(strategy => {
                this.checkedStrategiesIds.push(strategy.id)
                this.checkedStrategiesIdsOptions.push({
                    value: strategy.id,
                    text: strategy.name
                })
            })
        }
    }
</script>

<style scoped>

</style>
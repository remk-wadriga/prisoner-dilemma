<template src="@/templates/game/save-game-results.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "SaveGameResults",
        data () {
            return {
                onCloseCallbackFunction: () => {  },
                params: {
                    name: null,
                    description: null
                }
            }
        },
        props: {
            totalResults: Array,
            individualResults: Object
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
            save () {
                if (!this.params.name) {
                    return false
                }

                this.params.results = {
                    total: this.totalResults,
                    individual: this.individualResults
                }

                Api.methods.request('save_game_url', this.params, 'POST', response => {
                    this.$parent.saveGameResultsModalVisible = false
                    this.onCloseCallback
                    console.log(response)
                })
            },
            close () {
                this.$refs.saveGameResultsModalRef.hide()
                this.$parent.saveGameResultsModalVisible = false
                this.onCloseCallback
            }
        },
        mounted() {
            this.$refs.saveGameResultsModalRef.show()
        }
    }
</script>

<style scoped>

</style>
<template src="@/templates/strategy/delete.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "Delete",
        methods: {
            deleteStrategy() {
                let id = this.$route.params.id
                if (id === undefined) {
                    id = this.$store.state.strategy.selected.id
                }
                Api.methods.request(['strategy_url', {id}], {}, 'DELETE', () => {
                    this.$parent.deleteStrategyVisible = false
                    this.$store.commit('selectedStrategy', null)
                    let callback = this.$store.state.app.closeModalCallback
                    if (callback !== undefined) {
                        callback();
                    } else {
                        this.$router.push({name: 'app_homepage'})
                    }
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
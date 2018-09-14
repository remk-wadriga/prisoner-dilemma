<template src="@/templates/strategy/delete.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "Delete",
        methods: {
            deleteStrategy() {
                let id = this.$route.params.id
                if (id === undefined) {
                    id = this.$store.state.strategy.selectedId
                }
                Api.methods.request(['strategy_url', {id}], {}, 'DELETE', () => {
                    this.$parent.deleteStrategyVisible = false
                    this.$router.push({name: 'app_homepage'})
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
<template src="@/templates/strategy/form.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "Form",
        data() {
            return {
                name: null,
                description: null,
                status: 'enabled',
            }
        },
        methods: {
            submitStrategyFrom() {
                // strategy_form
                let data = {
                    strategy_form: {
                        name: this.name,
                        description: this.description,
                        status: this.status
                    }
                }
                Api.request('strategy_create_url', data, 'POST', response => {
                    console.log(response)
                })
            }
        },
        mounted() {
            console.log(this.$store.state.strategy.selected)
        }
    }
</script>

<style scoped>

</style>
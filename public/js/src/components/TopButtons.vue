<template<template src="@/templates/top-buttons.html" />

<script>
    import DeleteStrategy from '@/components/strategy/Delete.vue'

    export default {
        name: "TopButtons",
        components: { DeleteStrategy },
        data() {
            return {
                btnSize: 'lg',
                deleteStrategyVisible: false,
            }
        },
        computed: {
            buttons() {
                return this.$store.state.app.pageTopButtons
            }
        },
        methods: {
            btnClick(element) {
                element = JSON.parse(element)
                if (element.url !== undefined) {
                    this.$router.push(element.url)
                } else {
                    eval('this.' + element + '()')
                }
            },
            openDeleteStrategyModal() {
                this.deleteStrategyVisible = true
            }
        }
    }
</script>

<style scoped>

</style>
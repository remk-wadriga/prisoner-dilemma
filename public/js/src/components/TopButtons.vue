<template<template src="@/templates/top-buttons.html" />

<script>
    import DeleteStrategy from '@/components/strategy/Delete'

    export default {
        name: "TopButtons",
        components: { DeleteStrategy },
        data() {
            return {
                btnSize: '',
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
                // Set delete popup "onClose" callback function
                DeleteStrategy.computed.onCloseCallback = () => {
                    // Now delete popup is not visible
                    this.deleteStrategyVisible = false
                    // Go to homepage (strategies list)
                    this.$router.push({name: 'app_homepage'})
                }
                // Now delete popup is visible
                this.deleteStrategyVisible = true
            },
        }
    }
</script>

<style scoped>

</style>
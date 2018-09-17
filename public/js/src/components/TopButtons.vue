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
                this.$store.commit('setCloseModalCallback', () => {
                    this.$store.commit('setCloseModalCallback', null)
                    this.$router.push({name: 'app_homepage'})
                })
                this.deleteStrategyVisible = true
            },
        }
    }
</script>

<style scoped>

</style>
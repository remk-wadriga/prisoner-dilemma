<template src="@/templates/breadcrumbs.html" />

<script>
    export default {
        name: "Breadcrumbs",
        computed: {
            items() {
                let items = [];
                this.$store.state.app.breadcrumbs.forEach(item => {
                    //let url = this.$router.stringifyPath(item.url)
                    let newItem = {text: item.title}
                    if (typeof item.url === 'string') {
                        newItem['to'] = {name: item.url}
                    } else {
                        newItem['to'] = item.url
                    }
                    items.push(newItem)
                })
                return items
            }
        }
    }
</script>

<style scoped>

</style>
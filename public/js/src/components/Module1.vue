<template>
    <h1>{{name}}</h1>
</template>

<script>
    export default {
        name: "Module1",
        data() {
            return {
                name: 'Module1'
            }
        }
    }
</script>

<style scoped>
    h1  {
        color: red;
    }
</style>
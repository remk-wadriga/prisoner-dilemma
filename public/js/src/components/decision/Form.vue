<template src="@/templates/decision/form.html" />

<script>
    import TreeEditor from '@/components/TreeEditor'

    export default {
        name: "Form",
        components: { TreeEditor },
        data() {
            return {
                decisionsOptions: null,
                decisionsData: null
            }
        },
        mounted() {
            this.decisionsOptions = {
                legend: [
                    {
                        type: 'accept',
                        color: 'green',
                        label: 'Accept',
                        shortLabel: 'A'
                    },
                    {
                        type: 'refuse',
                        color: 'red',
                        label: 'Refuse',
                        shortLabel: 'R'
                    }
                ],
                children: [
                    {label: 'yes'},
                    {label: 'no'}
                ]
            };

            this.decisionsData = this.$store.state.strategy.decisionsData
        },
    }
</script>

<style scoped>

</style>
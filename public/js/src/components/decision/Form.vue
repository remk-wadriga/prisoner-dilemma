<template src="@/templates/decision/form.html" />

<script>
    import TreeEditor from '@/components/TreeEditor'

    export default {
        name: "Form",
        components: { TreeEditor },
        data() {
            return {
                decisionsOptions: {
                    legend: [
                        {
                            type: 'accept',
                            color: 'green',
                            label: 'Yes',
                            shortLabel: 'Y'
                        },
                        {
                            type: 'refuse',
                            color: 'red',
                            label: 'No',
                            shortLabel: 'N'
                        },
                        {
                            type: 'random',
                            color: 'blue',
                            label: 'Random',
                            shortLabel: 'R'
                        },
                        {
                            type: 'copy',
                            color: 'gray',
                            label: 'Copy',
                            shortLabel: 'C'
                        }
                    ],
                    children: [
                        {label: 'yes'},
                        {label: 'no'}
                    ]
                }
            }
        },
        props: {
            decisionsData: Object
        },
        methods: {
            getDecisionsData() {
                return this.$refs.treeEditor.getData()
            }
        }
    }
</script>

<style scoped>

</style>
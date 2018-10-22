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
                },
                changedDecisionsData: null
            }
        },
        props: {
            decisionsData: Object
        },
        methods: {
            changeDecisionsData(data) {
                this.changedDecisionsData = data
            }
        },
        watch: {
            changedDecisionsData: {
                handler: function() {
                    this.$emit('changeDecisionsData', this.changedDecisionsData);
                },
                deep: true
            }
        }
    }
</script>

<style scoped>

</style>
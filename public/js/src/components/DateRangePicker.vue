<template src="@/templates/date-range-picker.html" />

<script>
    import VueRangePicker from 'vue-rangedate-picker'
    import Formatter from '@/helpers/Formatter'

    export default {
        name: "DateRangePicker",
        components: { VueRangePicker },
        data() {
            return {
                format: 'YYYY-MM-DD',
                startActiveYear: null,
                startActiveMonth: null,
                isReady: false
            }
        },
        props: {
            selectedDates: {type: Object, default() { return {start: null, end: null} }}
        },
        methods: {
            onDateSelected(selected) {
                this.startDate = selected.start
                this.endDate = selected.end

                this.$emit('setDatesRange', {start: this.startDate, end: this.endDate})
            }
        },
        computed: {
            startDate: {
                set(date) { this.selectedDates.start = typeof date === 'string' ? new Date(date) : date },
                get() { return Formatter.methods.formatDate(this.selectedDates.start) }
            },
            endDate: {
                set(date) { this.selectedDates.end = typeof date === 'string' ? new Date(date) : date },
                get() { return Formatter.methods.formatDate(this.selectedDates.end) }
            }
        },
        mounted() {
            if (this.startDate === null) {
                const n = new Date()
                this.startDate = new Date(n.getFullYear(), n.getMonth(), n.getDate() - 1)
            }
            if (this.endDate === null) {
                this.endDate = new Date()
            }

            const start = typeof this.selectedDates.start === 'string' ? new Date(this.selectedDates.start) : this.selectedDates.start
            if (start.getMonth() === 0) {
                this.startActiveYear = start.getFullYear() - 1
                this.startActiveMonth = 11
            } else {
                this.startActiveYear = start.getFullYear()
                this.startActiveMonth = start.getMonth() - 1
            }

            this.isReady = true
        }
    }
</script>

<style scoped>

</style>
<script>
    import { Bar } from 'vue-chartjs'

    export default {
        extends: Bar,
        props: {
            labels: Array,
            data: Array,
            options: Object
        },
        data () {
            return {

            }
        },
        mounted () {
            let options = {
                responsive: true,
                maintainAspectRatio: false
            }
            if (this.options) {
                options = this.options
            }
            if (!this.data) {
                this.data = []
            }

            this.data.forEach(data => {
                if (data.backgroundColor === undefined) {
                    data.backgroundColor = '#FC2525'
                }
                if (data.pointBackgroundColor === undefined) {
                    data.pointBackgroundColor = 'white'
                }
                if (data.borderWidth === undefined) {
                    data.borderWidth = 1
                }
                if (data.pointBorderColor === undefined) {
                    data.pointBorderColor = '#05CBE1'
                }
            })

            this.renderChart({
                labels: this.labels,
                datasets: this.data
            }, options)
        }
    }
</script>

<style scoped>

</style>
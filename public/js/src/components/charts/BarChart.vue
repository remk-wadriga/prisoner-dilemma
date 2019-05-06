<script>
    import { Bar } from 'vue-chartjs'

    export default {
        extends: Bar,
        props: {
            labels: Array,
            data: Array,
            options: Object,
            tooltipTitleCallback: Function,
            tooltipLabelCallback: Function
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

            if (this.tooltipTitleCallback || this.tooltipLabelCallback) {
                if (options.tooltips === undefined) {
                    options.tooltips = {
                        callbacks: {}
                    }
                }
                if (this.tooltipTitleCallback) {
                    options.tooltips.callbacks.title = this.tooltipTitleCallback
                }
                if (this.tooltipLabelCallback) {
                    options.tooltips.callbacks.label = this.tooltipLabelCallback
                }
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

<style>
    .chart-container {
        background: #212733;
        border-radius: 15px;
        box-shadow: 0px 2px 15px rgba(25, 25, 25, 0.27);
        margin:  25px 0;
        width: 100%;
    }
    .chart-container h2 {
        margin-top: 0;
        padding: 15px 0;
        color:  rgba(255, 0,0, 0.5);
        border-bottom: 1px solid #323d54;
    }
</style>
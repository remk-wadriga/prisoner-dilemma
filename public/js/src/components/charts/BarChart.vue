<script>
    import { Bar } from 'vue-chartjs'

    export default {
        extends: Bar,
        props: {
            labels: Array,
            data: Array,
            options: Object,
            tooltipTitleCallback: Function,
            tooltipLabelCallback: Function,
            onClick: Function
        },
        data () {
            return {
                borderColors: ['#FC2525', '#05CBE1'],
                backgroundColors: [
                    ['rgba(255, 0,0, 0.5)', 'rgba(255, 0, 0, 0.25)', 'rgba(255, 0, 0, 0)'],
                    ['rgba(0, 231, 255, 0.9)', 'rgba(0, 231, 255, 0.25)', 'rgba(0, 231, 255, 0)']
                ]
            }
        },
        watch: {
            data() {
                this.render()
            }
        },
        methods: {
            render() {
                let options = {
                    responsive: true,
                    maintainAspectRatio: false
                }
                if (this.onClick) {
                    options.onClick = this.onClick
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

                let borderColorIndex = 0
                let backgroundColorIndex = 0
                this.data.forEach(data => {
                    if (data.borderColor === undefined) {
                        let borderColor = this.borderColors[borderColorIndex++]
                        if (borderColor === undefined) {
                            borderColorIndex = 0
                            borderColor = this.borderColors[borderColorIndex++]
                        }
                        data.borderColor = borderColor
                    }
                    if (data.pointBackgroundColor === undefined) {
                        data.pointBackgroundColor = 'white'
                    }
                    if (data.borderWidth === undefined) {
                        data.borderWidth = 1
                    }
                    if (data.pointBorderColor === undefined) {
                        data.pointBorderColor = 'white'
                    }
                    if (data.backgroundColor === undefined) {
                        let backgroundColors = this.backgroundColors[backgroundColorIndex++]
                        if (backgroundColors === undefined) {
                            backgroundColorIndex = 0
                            backgroundColors = this.backgroundColors[backgroundColorIndex++]
                        }

                        let gradient = this.$refs.canvas.getContext('2d').createLinearGradient(0, 0, 0, 450)
                        gradient.addColorStop(0, backgroundColors[0])
                        gradient.addColorStop(0.5, backgroundColors[1])
                        gradient.addColorStop(1, backgroundColors[2])
                        data.backgroundColor = gradient
                    }
                })

                this.renderChart({
                    labels: this.labels,
                    datasets: this.data
                }, options)
            }
        },
        mounted () {
            this.render()
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
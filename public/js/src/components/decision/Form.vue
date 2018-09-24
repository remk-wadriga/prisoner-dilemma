<template src="@/templates/decision/form.html" />

<script>
    import { Diagram } from 'vue-diagrams'

    export default {
        name: "Form",
        components: { Diagram },
        data() {
            return {
                model: null,
                nodeWidth: 80,
                nodeHeight: 100,
                posX: -5,
                posY: 385,
            }
        },
        computed: {

        },
        watch: {

        },
        methods: {
            calculateCurrentXAndY() {
                let nodes = this.model._model.nodes
                if (nodes !== undefined && nodes && nodes.length > 0) {
                    let isXChanged = false
                    let isYChanged = false

                    nodes.forEach(node => {
                        if (node.x > this.posX) {
                            this.posX = node.x
                            isXChanged = true
                        }
                        if (node.y < this.posY) {
                            this.posY = node.y
                            isYChanged = true
                        }
                    })

                    if (isXChanged) {
                        this.posX += this.nodeWidth + 70
                    }
                    if (isYChanged) {
                        this.posY -= (this.nodeHeight / 2 + 10)
                    }
                }
            },
            addDecision(type) {
                let name = ''
                let node = null
                switch (type) {
                    case 'accept':
                        name = 'Accept'
                        node = this.model.addNode('Accept', this.posX, this.posY, this.nodeWidth, this.nodeHeight)
                        node.color = 'green'
                        break
                    case 'refuse':
                        name = 'Refuse'
                        node = this.model.addNode('Refuse', this.posX, this.posY, this.nodeWidth, this.nodeHeight)
                        node.color = 'red'
                        break
                    case 'random':
                        name = 'Random'
                        node = this.model.addNode('Random', this.posX, this.posY, this.nodeWidth, this.nodeHeight)
                        node.color = 'blue'
                        break
                    default:
                        console.log('Invalid decision type: "' + type + '"')
                        return
                }

                node.addInPort('');
                node.addOutPort('Accept')
                node.addOutPort('Refuse')

                this.posY += (this.nodeHeight + 20)
            }
        },
        created() {
            this.model = new Diagram.Model()
            let decisionsData = this.$store.state.strategy.decisionsData
            if (decisionsData !== null && decisionsData !== undefined && decisionsData !== '') {
                this.model._model = decisionsData
                this.calculateCurrentXAndY()
            }
            this.$store.commit('setStrategyDecisionsFormModel', this.model)
        },
        mounted() {

        },
    }
</script>

<style scoped>

</style>
<template src="@/templates/decision/form.html" />

<script>
    import { Diagram } from 'vue-diagrams'

    export default {
        name: "Form",
        components: { Diagram },
        data() {
            return {
                model: null,
                level: 0,
                nodes: {},
                nodeWidth: 80,
                nodeHeight: 100,
                posX: -5,
                posY: 385,
                lastLevelYPos: null,
            }
        },
        computed: {

        },
        watch: {

        },
        methods: {
            addDecision(type) {
                let levelIndex = 'level_' + this.level

                if (this.nodes[levelIndex] === undefined) {
                    this.nodes[levelIndex] = []
                } else if (this.level === 0) {
                    this.level++
                    levelIndex = 'level_' + this.level
                    this.nodes[levelIndex] = []
                    this.posX += this.nodeWidth + 70
                    this.posY -= (this.nodeHeight / 2 + 10)
                } else {
                    this.posY += (this.nodeHeight + 20)
                }

                if (this.lastLevelYPos === null) {
                    this.lastLevelYPos = this.posY
                }

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

                if (this.level > 0) {
                    node.addInPort('')
                }

                node.addOutPort("accept")
                node.addOutPort("refuse")

                this.nodes[levelIndex].push(node)

                if (this.nodes[levelIndex].length === this.level * 2) {
                    this.level++
                    this.posX += this.nodeWidth + 70
                    this.lastLevelYPos -= (this.nodeHeight + 30)
                    this.posY = this.lastLevelYPos
                }
            }
        },
        created() {
            this.model = new Diagram.Model()
            let decisionsJsonData = this.$store.state.strategy.decisions
            if (decisionsJsonData !== null && decisionsJsonData !== undefined && decisionsJsonData !== '') {
                if (typeof decisionsJsonData !== 'string') {
                    decisionsJsonData = JSON.stringify(decisionsJsonData)
                }
                this.model.deserialize(decisionsJsonData)
            }
            this.$store.commit('setStrategyDecisionsFormModel', this.model)
        },
        mounted() {

        },
    }
</script>

<style scoped>

</style>
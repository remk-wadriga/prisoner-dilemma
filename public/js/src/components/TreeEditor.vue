<template>
    <div id="tree_editor_container"></div>
</template>

<script>
    import { TreeEditor } from '@/tree_editor/src/TreeEditor.js'

    export default {
        name: "TreeEditor",
        data() {
            return {
                editor: null,
                tmpIndex: 1,
                decisionsData: null
            }
        },
        props: {
            options: Object,
            data: Object
        },
        methods: {
            getData() {
                return this.editor.getData()
            }
        },
        mounted() {
            this.editor = new TreeEditor('#tree_editor_container', this.options)
            this.editor.setData(this.data)
        }
    }
</script>

<style>
    svg {
        user-select: none;
        margin: auto;
        display: block;
        font-size: 12px;
    }

    .axis .domain {
        display: none;
    }

    .axis.x .tick line {
        stroke: #ddd;
        opacity: 0.8;
    }


    g.legend {
        cursor: pointer;
    }

    g.legend text.short-label {
        text-anchor: middle;
        fill: black;
    }

    g.legend text.short-label tspan {
        dominant-baseline: central;
    }

    g.legend text.label {
        text-anchor: start;
        fill: black;
    }

    g.legend text.label tspan {
        dominant-baseline: central;
    }

    g.node circle {
        fill: white;
        stroke: #ccc;
        cursor: pointer;
    }

    g.node circle.selected {
        stroke-width: 2;
        stroke: black;
    }

    g.node text {
        fill: black;
        text-anchor: middle;
        cursor: pointer;
    }

    g.node text tspan {
        dominant-baseline: central;
    }

    g.link path {
        stroke: #ccc;
        fill: white;
        cursor: pointer;
    }

    g.link path.selected {
        stroke: black;
        stroke-dasharray: 10,2;
    }

    path.dragline {
        stroke: #ccc;
        fill: white;
        pointer-events: none;
    }

    path.dragline.hidden {
        stroke-width: 0;
    }

    g.link textPath {
        text-anchor: middle;
        fill: black;
        cursor: pointer;
    }
</style>
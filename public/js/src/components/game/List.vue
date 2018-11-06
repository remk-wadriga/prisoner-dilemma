<template src="@/templates/game/list.html" />

<script>
    import Api from '@/helpers/Api'
    import DeleteModal from '@/components/game/DeleteModal'

    export default {
        name: "List",
        components: { DeleteModal },
        data() {
            return {
                games: [],
                fields: {
                    name: {
                        sortable: true
                    },
                    date: {
                        sortable: true
                    },
                    sum: {
                        sortable: true
                    },
                    shortDescription: {
                        label: 'Description',
                        sortable: false
                    },
                    actions: {
                        label: 'Actions'
                    },
                },
                viewBtnVar: 'primary',
                updateBtnVar: 'success',
                deleteBtnVar: 'danger',
                currentPage: 1,
                perPage: 10,
                totalRows: 0,
                selectedGame: null,
                deleteGameVisible: false,
            }
        },
        methods: {
            viewGame(game) {
                this.$router.push({name: 'game_view', params: {id: game.id}})
            },
            openDeleteGameModal(game) {
                this.selectedGame = game
                this.deleteGameVisible = true
            },
            onCloseDeleteModalCallback() {
                Api.methods.request(['game_url', {id: this.selectedGame.id}], {}, 'DELETE', response => {
                    this.selectedGame = null
                    this.deleteGameVisible = false
                    this.$router.go(0)
                })
            }
        },
        mounted() {
            Api.methods.request('games_list_url', {}, 'GET', response => {
                response.forEach(item => {
                    if (item.description !== null) {
                        item.shortDescription = item.description.substring(0, 150) + '...'
                    }
                    this.totalRows++
                })
                this.games = response
                this.$store.commit('setPageTitle', '')
                this.$store.commit('setContentTitle', 'Games')
                this.$store.commit('setBreadcrumbs', [{title: 'Games', url: 'games_list'}])
                this.$store.commit('setPageTopButtons', [])
            })
        }
    }
</script>

<style scoped>

</style>
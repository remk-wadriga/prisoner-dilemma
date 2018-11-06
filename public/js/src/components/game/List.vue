<template src="@/templates/game/list.html" />

<script>
    import Api from '@/helpers/Api'

    export default {
        name: "List",
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
            }
        },
        methods: {
            viewGame(game) {
                this.$router.push({name: 'game_view', params: {id: game.id}})
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
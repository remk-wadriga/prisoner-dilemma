<template src="@/templates/user/account.html" />

<script>
    import user from '@/entityes/User'
    import Api from '@/helpers/Api'

    export default {
        name: "Account",
        data() {
            return {
                email: null,
                firstName: null,
                lastName: null,
                password: null,
                repeatPassword: null
            }
        },
        methods: {
            updateAccount() {
                let data = {
                    user_form: {
                        email: this.email,
                        firstName: this.firstName,
                        lastName: this.lastName
                    }
                }
                if (this.password) {
                    data.user_form.plainPassword = {
                        first: this.password,
                        second: this.repeatPassword
                    }
                }

                Api.methods.request('user_info', data, 'PUT', response => {
                    user.methods.setInfo(response)
                    this.$store.commit('addLogMessage', {type: 'info', text: 'Your profile was success fully updated'})
                })
            }
        },
        mounted() {
            this.$store.commit('setContentTitle', 'My profile')
            this.$store.commit('setBreadcrumbs', [
                {title: 'Profile', url: 'user_account'},
            ])
            this.$store.commit('setPageTopButtons', [
                {title: 'Create new strategy', type: 'primary', click: {url: {name: 'strategy_create'}}},
                {title: 'Start new game', type: 'success', click: {url: {name: 'game_start'}}},
                {title: 'Start new tournament', click: {url: {name: 'tournament_start'}}}
            ])

            let userInfo = user.methods.getInfo()
            if (userInfo === null) {
                this.$store.commit('addLogMessage', {type: 'danger', text: 'Can`t get user info. Try lo login again'})
                this.$router.push({name: 'app_homepage'})
            } else {
                if (userInfo.email) {
                    this.email = userInfo.email
                }
                if (userInfo.firstName) {
                    this.firstName = userInfo.firstName
                }
                if (userInfo.lastName) {
                    this.lastName = userInfo.lastName
                }
            }
        }
    }
</script>

<style scoped>

</style>
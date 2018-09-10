<template src="@/templates/index/login.html" />

<script>
    import Api from '@/helpers/Api'
    import user from '@/entityes/User'

    export default {
        name: "Login",
        data() {
            return {
                username: 'user@gmail.com',
                password: 'test'
            }
        },
        methods: {
            loginUser() {
                let data = {username: this.username, password: this.password};
                Api.methods.request('security_login', data, 'POST', (response) => {
                    user.methods.login(response)
                    this.$router.push({name: 'app_homepage'})
                })
            }
        },
        mounted() {
            if (user.methods.isLogged()) {
                this.$router.go(-1)
            } else {
                this.$store.commit('setPageTitle', 'Login page')
            }
        }
    }
</script>

<style scoped>

</style>
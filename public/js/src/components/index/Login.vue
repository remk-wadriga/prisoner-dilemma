<template src="@/templates/index/login.html" />

<script>
    import Api from '@/helpers/Api'
    import user from '@/stores/User'

    export default {
        name: "Login",
        data() {
            return {
                username: 'user@gmail.com',
                password: 'test',
                user: user
            }
        },
        methods: {
            loginUser() {
                let data = {username: this.username, password: this.password};
                Api.methods.request('security_login', data, 'POST', response => {
                    user.methods.setAccessToken(response.access_token)
                    user.methods.setRenewToken(response.renew_token)
                })
            }
        }
    }
</script>

<style scoped>

</style>
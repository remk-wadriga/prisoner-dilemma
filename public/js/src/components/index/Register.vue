<template src="@/templates/index/register.html" />

<script>
    import Api from '@/helpers/Api'
    import user from '@/entityes/User'

    export default {
        name: "Register",
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
            registerUser() {
                let data = {
                    user_form: {
                        email: this.email,
                        firstName: this.firstName,
                        lastName: this.lastName,
                        plainPassword: {
                            first: this.password,
                            second: this.repeatPassword
                        }
                    }
                }
                Api.methods.request('security_registration', data, 'POST', response => {
                    user.methods.login(response)
                    this.$router.push({name: 'app_homepage'})
                })
            }
        },
        mounted() {
            if (user.methods.isLogged()) {
                this.$router.go(-1)
            } else {
                this.$store.commit('setPageTitle', 'Register page')
            }
        }
    }
</script>

<style scoped>

</style>
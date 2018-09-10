<template src="./templates/app.html" />

<script>

import Logger from '@/components/Logger'
import user from '@/entityes/User'
import Api from '@/helpers/Api'
import LetMenu from '@/components/LeftMenu'
import Content from '@/components/Content'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

export default {
    name: 'App',
    components: { Logger, LetMenu, Content },
    data() {
        return {
            user: user
        }
    },
    methods: {
        logoutUser() {
            // security_logout
            Api.methods.request('security_logout', {}, 'POST', response => {
                user.methods.logout()
                this.$router.go(this.$router.currentRoute)
            })
        }
    },
    computed: {
        pageTitle() {
            return this.$store.state.app.pageTitle
        }
    }
}
</script>

<style src="./assets/font-awesome.css" />
<style src="./assets/app.css" />

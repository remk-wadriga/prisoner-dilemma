<template src="./templates/app.html" />

<script>

import Api from '@/helpers/Api'
import user from '@/entityes/User'
import Breadcrumbs from '@/components/Breadcrumbs'
import Logger from '@/components/Logger'
import LetMenu from '@/components/LeftMenu'
import Content from '@/components/Content'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import 'vue-orgchart/dist/style.min.css'

export default {
    name: 'App',
    components: { Logger, Breadcrumbs, LetMenu, Content },
    data() {
        return {
            user: user
        }
    },
    methods: {
        logoutUser() {
            let callback = response => {
                user.methods.logout()
                this.$router.push({name: 'app_homepage'})
                return false;
            };
            // security_logout
            Api.methods.request('security_logout', {}, 'POST', callback, {}, callback)
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

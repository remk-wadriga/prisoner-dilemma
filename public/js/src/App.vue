<template src="./templates/app.html" />

<script>

import Logger from '@/components/index/Logger'
import user from '@/entityes/User'
import Api from '@/helpers/Api'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
/*import 'bootstrap-vue/dist/bootstrap-vue.js'
import 'bootstrap-vue/dist/bootstrap-vue.common.js'
import 'bootstrap-vue/dist/bootstrap-vue.esm.js'*/

export default {
    name: 'App',
    components: { Logger },
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
    }
}
</script>

<style src="./assets/font-awesome.css" />
<style src="./assets/app.css" />

import Vue from 'vue'
import Router from 'vue-router'
import Login from '@/components/index/Login'
import Home from '@/components/index/Home'
import Register from '@/components/index/Register'
import Account from '@/components/user/Account'
import StrategyView from '@/components/strategy/View'

Vue.use(Router)

export default new Router({
    routes: [
        {
            path: '/',
            name: 'app_homepage',
            component: Home
        },
        {
            path: '/login',
            name: 'app_login',
            component: Login
        },
        {
            path: '/register',
            name: 'app_register',
            component: Register
        },
        {
            path: '/account',
            name: 'user_account',
            component: Account
        },
        {
            path: '/strategy/:id',
            name: 'strategy_view',
            component: StrategyView
        },
    ]
})

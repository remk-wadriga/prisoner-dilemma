import Vue from 'vue'
import Router from 'vue-router'
import HelloWorld from '@/components/HelloWorld'
import Friends from '@/components/Friends'
import Account from '@/components/Account'
import Contact from '@/components/Contact'

Vue.use(Router)

export default new Router({
  routes: [
      {
          path: '/',
          name: 'HelloWorld',
          component: HelloWorld
      },
      {
          path: '/friends',
          name: 'Friends',
          component: Friends
      },
      {
          path: '/account',
          name: 'Account',
          component: Account
      },
      {
          path: '/contact',
          name: 'Contact',
          component: Contact
      }
  ]
})

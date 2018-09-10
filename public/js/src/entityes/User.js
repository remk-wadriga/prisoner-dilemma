import Api from '@/helpers/Api'
import router from '@/router'

const User = {
    data: {
        accessToken: null,
        renewToken: null,
        info: null,
        name: null,
        email: null,
        firstName: null,
        lastName: null,
        password: null,
        repeatPassword: null
    },
    methods: {
        getAccessToken() {
            if (User.data.accessToken !== null) {
                return User.data.accessToken
            }
            return User.data.accessToken = localStorage.getItem('user.accessToken')
        },
        getRenewToken() {
            if (User.data.renewToken !== null) {
                return User.data.renewToken
            }
            return User.data.renewToken = localStorage.getItem('user.renewToken')
        },
        setAccessToken(token) {
            User.data.accessToken = token
            if (token === null) {
                localStorage.removeItem('user.accessToken')
            } else {
                localStorage.setItem('user.accessToken', token)
            }
        },
        setRenewToken(token) {
            User.data.renewToken = token
            if (token === null) {
                localStorage.removeItem('user.renewToken')
            } else {
                localStorage.setItem('user.renewToken', token)
            }
        },
        isLogged() {
            return User.methods.getAccessToken() !== null
        },
        login(data) {
            if (data.access_token !== undefined) {
                User.methods.setAccessToken(data.access_token)
            }
            if (data.renew_token !== undefined) {
                User.methods.setRenewToken(data.renew_token)
            }
        },
        logout() {
            User.methods.setAccessToken(null)
            User.methods.setRenewToken(null)
            User.methods.setInfo(null)
            User.methods.setName(null)
        },
        setInfo(info) {
            User.data.info = info
            if (info === null) {
                localStorage.removeItem('user.info')
            } else {
                localStorage.setItem('user.info', JSON.stringify(info))
                // Refresh name
                User.methods.setName(null)
            }
        },
        getInfo() {
            if (User.data.info !== null) {
                return User.data.info
            }
            let info = localStorage.getItem('user.info')
            if (info) {
                return User.data.info = JSON.parse(info)
            }
            Api.methods.request('user_info', {}, 'GET', data => {
                if (!data.email) {
                    store.commit('addLogMessage', {type: 'danger', text: 'Can`t get user info. Try lo login again'})
                    router.push({name: 'app_homepage'})
                }
                User.methods.setInfo(data)
            })
        },
        setName(name) {
            User.data.name = name
            if (name === null) {
                localStorage.removeItem('user.name')
            } else {
                localStorage.setItem('user.name', name)
            }
        },
        getName() {
            if (User.data.name !== null) {
                return User.data.name
            }
            let info = User.methods.getInfo()
            if (!info) {
                return null
            }
            let name = ''
            if (info.firstName) {
                name += info.firstName
            }
            if (info.lastName) {
                if (name !== '') {
                    name += ' '
                }
                name += info.lastName
            }
            if (name === '' && info.email) {
                name = info.email
            }
            User.methods.setName(name)
            return name
        }
    }
};

export default User;
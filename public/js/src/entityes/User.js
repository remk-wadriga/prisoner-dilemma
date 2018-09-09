import Api from "../helpers/Api";

const User = {
    data: {
        accessToken: null,
        renewToken: null,
        name: null
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
            User.methods.setName(null)
        },
        getName() {
            if (User.data.name !== null) {
                return User.data.name
            }
            let name = localStorage.getItem('user.name')
            if (name) {
                return User.data.name = name
            } else {
                name = null
            }
            Api.methods.request('security_user_info', {}, 'GET', data => {
                var name = ''
                if (data.firstName !== undefined) {
                    name += data.firstName
                }
                if (data.lastName !== undefined) {
                    if (name !== '') {
                        name += ' '
                    }
                    name += data.lastName
                }
                if (name === '' && data.email !== null) {
                    name = data.email
                }
                User.methods.setName(name)
            })
        },
        setName(name) {
            User.data.name = name
            if (name === null) {
                localStorage.removeItem('user.name')
            } else {
                localStorage.setItem('user.name', name)
            }
        }
    }
};

export default User;
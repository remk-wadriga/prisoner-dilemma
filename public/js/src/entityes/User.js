import Api from "../helpers/Api";

const User = {
    data: {
        accessToken: null,
        renewToken: null
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
            localStorage.setItem('user.accessToken', token)
        },
        setRenewToken(token) {
            User.data.renewToken = token
            localStorage.setItem('user.renewToken', token)
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
        }
    }
};

export default User;
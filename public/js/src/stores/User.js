const User = {
    data: {
        accessToken: null,
        renewToken: null
    },
    methods: {
        getAccessToken() { return User.data.accessToken },
        getRenewToken() { return User.data.renewToken },
        setAccessToken(token) { User.data.accessToken = token },
        setRenewToken(token) { User.data.renewToken = token },
        isLogged() { return User.methods.getAccessToken() !== null }
    }
};

export default User;
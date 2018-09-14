import user from '@/entityes/User'
import store from '@/store'
import router from '@/router'

var lastRequestParams = null;

const Api = {
    data: {
        baseUrl: 'http://strategy.local',
        routes: {
            'app_homepage': '/',
            'security_login': '/login',
            'security_logout': '/logout',
            'security_registration': '/registration',
            'security_renew_token': '/renew-token',
            'user_info': '/user',
            'strategy_url': '/strategy/:id',
            'strategy_create_url': '/strategy',
        }
    },
    methods: {
        getUrl(name) {
            let params = null;
            if (typeof name !== 'string') {
                params = name[1]
                name = name[0]
            }
            let path = Api.data.routes[name]
            if (path === undefined) {
                throw 'Url with name "' + name + '" is not found!';
            }
            if (params !== null) {
                Object.keys(params).forEach(key => {
                    path = path.replace(':' + key, params[key])
                })
            }
            return Api.data.baseUrl + path;
        },
        request(urlName, data = {}, method = 'GET', successCallback = null, headers = {}, errorCallback = null) {
            let url = Api.methods.getUrl(urlName);
            if (successCallback === null) {
                successCallback = response => {}
            }
            if (errorCallback === null) {
                errorCallback = response => { return true }
            }
            // Add default content-type header
            if (headers['Content-Type'] === undefined) {
                headers['Content-Type'] = 'application/json'
            }
            // Add access token to headers, ig user is logged
            if (headers['X-AUTH-TOKEN'] === undefined && user.methods.isLogged()) {
                headers['X-AUTH-TOKEN'] = user.methods.getAccessToken()
            }
            // Json decode data, if it`s "json" request
            if (headers['Content-Type'] === 'application/json' && typeof data !== 'string') {
                data = JSON.stringify(data)
            }
            var requestParams = {
                method: method,
                headers: headers
            }
            if (method !== 'GET') {
                requestParams.body = data
            }

            // Remember last request params
            if (urlName !== 'security_login' && urlName !== 'security_renew_token') {
                lastRequestParams = {url, requestParams, successCallback, errorCallback}
            }
            // Send request
            fetch(url, requestParams)
            .then(response => headers['Content-Type'] === 'application/json' ? response.json() : response)
            .then(response => {
                // Process response
                if (response.error === undefined) {
                    Api.methods.requestSuccess(response, successCallback)
                } else {
                    Api.methods.requestFailed(response.error, errorCallback)
                }
            })
        },
        requestSuccess(response, callback) {
            callback(response)
        },
        requestFailed(error, callback) {
            if (error.code !== undefined && error.message !== undefined) {
                // Call client error-callback function and check what is it returns
                // if it returns false - do not do anything else
                if (callback(error) === false) {
                    return;
                }

                // Error code "1001" means that access token is invalid, so, let`s logout user and go to the login page!
                if (error.code === 1001) {
                    user.methods.logout();
                    store.commit('addLogMessage', {type: 'info', text: error.message})
                    router.push({name: 'app_login'})
                // Error code "1002" means that access token is expired, so, let`s renew it!
                } else if (error.code === 1002) {
                    let renewToken = user.methods.getRenewToken();
                    if (!renewToken) {
                        store.commit('addLogMessage', {type: 'danger', text: 'Access token missing, try to login again'})
                        return;
                    }
                    Api.methods.request('security_renew_token', {renew_token: renewToken}, 'POST', (response) => {
                        // Login user with new tokens
                        user.methods.login(response)
                        // If las request prams is not null - remake last request
                        if (lastRequestParams !== null) {
                            // Check is response has "access_token" param
                            if (response.access_token === undefined) {
                                return;
                            }
                            // Set new request token for new request
                            lastRequestParams.requestParams.headers['X-AUTH-TOKEN'] = response.access_token;
                            // Remake request
                            fetch(lastRequestParams.url, lastRequestParams.requestParams)
                                .then(response => lastRequestParams.requestParams.headers['Content-Type'] === 'application/json' ? response.json() : response)
                                .then(response => {
                                    // Process response
                                    if (response.error === undefined) {
                                        Api.methods.requestSuccess(response, lastRequestParams.successCallback)
                                    } else {
                                        Api.methods.requestFailed(response.error, lastRequestParams.errorCallback)
                                    }
                                    // Clear last request params
                                    lastRequestParams = null
                                })
                        }
                    })
                // Error code "1003" means that user is not logged, so go to login page!
                } else if (error.code === 1003) {
                    store.commit('addLogMessage', {type: 'info', text: error.message})
                    router.push({name: 'app_login'})
                } else {
                    store.commit('addLogMessage', {type: 'danger', text: error.message})
                }
            }
        }
    }
};

export default Api;
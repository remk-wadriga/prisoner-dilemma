import user from '@/stores/User'

const Api = {
    data: {
        baseUrl: 'http://strategy.local',
        routes: {
            'app_homepage': '/',
            'security_login': '/login'
        }
    },
    methods: {
        getUrl(name) {
            let path = Api.data.routes[name]
            if (path === undefined) {
                throw 'Url with name "' + name + '" is not found!';
            }
            return Api.data.baseUrl + path;
        },
        request(urlName, data = {}, method = 'GET', callback = null, headers = {}, error = null) {
            if (callback === null) {
                callback = response => {}
            }
            if (error === null) {
                error = response => { console.log(response) }
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

            fetch(Api.methods.getUrl(urlName),requestParams)
            .then(response => headers['Content-Type'] === 'application/json' ? response.json() : response)
            .then(response => {
                if (response.error !== undefined) {
                    error(response.error)
                } else {
                    callback(response);
                }
            })
        }
    }
};

export default Api;
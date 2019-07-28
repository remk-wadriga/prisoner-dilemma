import user from '@/entityes/User'
import store from '@/store'
import router from '@/router'
import Config from '@/config.js'

var lastRequestParams = null;

const Api = {
    data: {
        baseUrl: Config.api.baseUrl,
        routes: {
            'app_homepage': '/',
            'security_login': '/login',
            'security_logout': '/logout',
            'security_registration': '/registration',
            'security_renew_token': '/renew-token',
            'user_info': '/user',
            'strategy_url': '/strategy/:id',
            'strategy_create_url': '/strategy',
            'create_random_strategy_url': '/strategy/random',
            'start_game_url': '/game/start',
            'params_strategy_url': '/params/strategy',
            'params_statistics_dates_url': '/params/statistics-dates',
            'params_game_filters': '/params/game-filters',
            'games_list_url': '/games',
            'params_game_url': '/params/game',
            'save_game_url': '/game',
            'game_url': '/game/:id',
            'game_results_url': '/game/:id/results',
            'total_statistics_by_dates_url': '/total-statistics-by-dates',
            'total_statistics_by_strategies_url': '/total-statistics-by-strategies',
            'total_statistics_by_games_url': '/total-statistics-by-games',
            'total_statistics_by_rounds_count_url': '/total-statistics-by-rounds-count',
            'strategy_statistics_dates': '/strategy/:id/statistics-dates',
            'strategy_statistics_by_dates_url': '/strategy/:id/statistics-by-dates',
            'strategy_statistics_by_rounds_count_url': '/strategy/:id/statistics-by-rounds-count',
            'game_statistics_by_strategies_url': '/game/:id/statistics-by-strategies',
            'game_statistics_by_dates_url': '/game/:id/statistics-by-dates',
            'params_tournament_url': '/params/tournament'
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
                    if (path.indexOf(':' + key) != -1) {
                        path = path.replace(':' + key, params[key])
                    } else if (params[key] !== null) {
                        if (path.indexOf('?') === -1) {
                            path += '?'
                        }
                        path += key + '=' + params[key] + '&'
                    }
                })
                if (path.indexOf('&') !== -1) {
                    path = path.substring(0, path.length - 1)
                }
            }
            return Api.data.baseUrl + path;
        },
        request(urlName, data = {}, method = 'GET', successCallback = null, headers = {}, errorCallback = null) {
            let url = Api.methods.getUrl(urlName)
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

            let requestParams = {
                method: method,
                headers: headers
            }
            if (method !== 'GET') {
                // Json decode data, if it`s "json" request
                if (headers['Content-Type'] === 'application/json' && typeof data !== 'string') {
                    data = JSON.stringify(data)
                }
                requestParams.body = data
            }

            // Remember the request url
            let requestUrl = method + ' ' + url
            if (requestUrl.indexOf('?') === -1) {
                requestUrl += '?'
            } else {
                requestUrl += '&'
            }
            requestUrl += 'access_token=' + user.methods.getAccessToken()

            let responseHeaders = null


            // Remember last request params
            if (urlName !== 'security_login' && urlName !== 'security_renew_token') {
                lastRequestParams = {url, requestParams, successCallback, errorCallback}
            }

            // Send request
            fetch(url, requestParams)
            .then(response => { responseHeaders = response.headers; return response })
            .then(response => headers['Content-Type'] === 'application/json' ? response.json() : response)
            .then(response => {
                // Process response
                if (response.error === undefined) {
                    Api.methods.requestSuccess(response, successCallback, requestUrl, responseHeaders, method)
                } else {
                    Api.methods.requestFailed(response.error, errorCallback, requestUrl, responseHeaders, method)
                }
            })
        },
        requestSuccess(response, callback, requestUrl, responseHeaders, method) {
            callback(response)
            this.processResponse(response, requestUrl, responseHeaders, method)
        },
        requestFailed(error, callback, requestUrl, responseHeaders, method) {
            if (error.code !== undefined && error.message !== undefined) {
                // Call client error-callback function and check what is it returns
                // if it returns false - do not do anything else
                if (callback(error) === false) {
                    return;
                }

                // Process response
                this.processResponse(error, requestUrl, responseHeaders, method)

                // Error code "1001" means that access token is invalid, so, let`s logout user and go to login page!
                if (error.code === 1001) {
                    user.methods.logout();
                    store.commit('addLogMessage', {type: 'info', text: error.message})
                    router.push({name: 'app_login'})
                // Error code "1002" means that access token is expired, so, let`s renew it!
                } else if (error.code === 1002) {
                    let renewToken = user.methods.getRenewToken();
                    if (!renewToken) {
                        store.commit('addLogMessage', {type: 'danger', text: 'Access token is expired, try to login again'})
                        return;
                    }
                    Api.methods.request('security_renew_token', {renew_token: renewToken}, 'POST', (response) => {
                        // Login user with new tokens
                        user.methods.login(response)
                        // If last request prams is not null - remake last request
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
        },
        processResponse(response, requestUrl, responseHeaders, method) {
            if (Config.params.env !== 'DEV') {
                return
            }

            if (responseHeaders && responseHeaders.get('debug-request-uri')) {
                requestUrl = method + ' ' + Api.data.baseUrl + responseHeaders.get('debug-request-uri')
            }

            store.commit('addDebugMessage', {
                requestUrl: requestUrl,
                debugToken: responseHeaders ? responseHeaders.get('x-debug-token') : null,
                debugPanelUrl: responseHeaders ? Api.data.baseUrl + '/_wdt/' + responseHeaders.get('x-debug-token') : null,
                debugUrl: responseHeaders ? responseHeaders.get('x-debug-token-link') : requestUrl
            })
        }
    }
};

export default Api;
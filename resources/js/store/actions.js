import { router } from "../app.js"

let actions = {
    LOGIN({ commit }, credentials) {
        axios.post('/api/login', credentials).then(res => {
            localStorage.setItem('token', res.data.access_token);
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + res.data.access_token;
            this.dispatch('GET_USER');
            router.replace({ name: 'Dashboard' });
        }).catch(err => {
            var error = "An error has occured.";
            if (err.response.status == 401) {
                error = err.response.data.message;
            } else if (err.response.status == 422) {
                if (err.response.data instanceof Array) {
                    error = "";
                    err.response.data.forEach((message) => {
                        error += message + "<br>";
                    });
                } else if (err.response.data.message != null) {
                    error = err.response.data.message;
                }
            }
            Vue.toasted.error(error);
        });
    },
    GET_USER({ commit }) {
        axios.get('/api/user')
            .then(res => {
                commit('SET', ['user', res.data]);
                if (router.currentRoute.name == 'Login') {
                    router.replace({ name: 'Dashboard' });
                }
            }).catch(err => {
                commit('RESET');
                if (router.currentRoute.name != 'Login') {
                    router.replace({ name: 'Login' });
                }
            });
    },
    GET_CLAIM({ commit }, id) {
        commit('SET', ['claim', null]);
        axios.get('/api/claims/details?id=' + id)
            .then(res => {
                commit('SET', ['claim', res.data.claim]);
            }).catch(err => {
                var error = "An error has occured.";
                if (err.response.status == 422) {
                    if (err.response.data instanceof Array) {
                        error = "";
                        err.response.data.forEach((message) => {
                            error += message + "<br>";
                        });
                    } else if (err.response.data.message != null) {
                        error = err.response.data.message;
                    }
                }
                Vue.toasted.error(error);
            });
    },
    GET_WARRANTY({ commit }, id) {
        commit('SET', ['warranty', null]);
        axios.get('/api/warranties/details?id=' + id)
            .then(res => {
                commit('SET', ['warranty', res.data.warranty]);
            }).catch(err => {
                var error = "An error has occured.";
                if (err.response.status == 422) {
                    if (err.response.data instanceof Array) {
                        error = "";
                        err.response.data.forEach((message) => {
                            error += message + "<br>";
                        });
                    } else if (err.response.data.message != null) {
                        error = err.response.data.message;
                    }
                }
                Vue.toasted.error(error);
            });
    },
    GET_MOTOR({ commit }, id) {
        commit('SET', ['motor', null]);
        axios.get('/api/motors/details?id=' + id)
            .then(res => {
                commit('SET', ['motor', res.data.motor]);
            }).catch(err => {
                var error = "An error has occured.";
                if (err.response.status == 422) {
                    if (err.response.data instanceof Array) {
                        error = "";
                        err.response.data.forEach((message) => {
                            error += message + "<br>";
                        });
                    } else if (err.response.data.message != null) {
                        error = err.response.data.message;
                    }
                }
                Vue.toasted.error(error);
            });
    },
    API({ commit }, inputs) {
        commit('SET', ['loading', true]);

        return new Promise((resolve, reject) => {
            var options = {
                method: inputs.method,
                url: inputs.url
            };
            if (inputs.method == 'get') {
                options.params = inputs;
            } else {
                options.data = inputs;
            }
            axios(options)
                .then(res => {
                    Vue.toasted.success(res.data.message);
                    resolve(res.data);
                }).catch(err => {
                    if (err.response.status == 401) {
                        commit('RESET');
                        router.replace({ name: 'Login' });
                    } else {
                        var error = "An error has occured.";
                        if (err.response.status == 422) {
                            if (err.response.data instanceof Array) {
                                error = "";
                                err.response.data.forEach((message) => {
                                    error += message + "<br>";
                                });
                            } else if (err.response.data.errors != null) {
                                var get_error = err.response.data.errors;
                                for (var a in get_error) {
                                    error += "<br>" + get_error[a][0] + "";
                                }
                            } else if (err.response.data.message != null) {
                                error = err.response.data.message;
                            }
                        }
                        Vue.toasted.error(error);
                        reject();
                    }
                }).finally(() => commit('SET', ['loading', false]));
        });
    },
    LOGOUT({ commit }) {
        axios.get('/api/logout').finally(function () {
            commit('RESET');
            router.replace({ name: 'Login' });
        });
    }
}
export default actions

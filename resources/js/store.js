import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        user: false,
        drawer: true,
        isLogged: false,
        token: ""
    },
    getters: {
        loggedInUser(state) {
            return state.user;
        }
    },
    mutations: {
        updateUser(state, data) {
            state.user = data;
        },
        changeDrawer(state) {
            state.drawer = !state.drawer;
        },
        updateLogged(state, data) {
            state.isLogged = data;
        },
        logoutUser(state) {
            state.user = false;
            state.isLogged = false;
            state.token = "";
        }
    },
    actions: {}
});

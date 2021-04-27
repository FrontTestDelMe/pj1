/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("./bootstrap");

window.Vue = require("vue").default;
window.Fire = new Vue();

import Vuetify from "../plugins/vuetify";

import router from "./router";
import store from "./store";
import App from "./components/App.vue";
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

require("./components");

import LaravelPermissionToVueJS from "laravel-permission-to-vuejs";
Vue.use(LaravelPermissionToVueJS);

import Vuelidate from "vuelidate";
Vue.use(Vuelidate);

import VueToastr from "vue-toastr";

Vue.use(VueToastr, {
    defaultTimeout: 3000,
    defaultPosition: "toast-top-right",
    defaultProgressBar: false,
    defaultProgressBarValue: 0
});

// ES6 Modules or TypeScript
import Swal from "sweetalert2";
window.Swal = Swal;

const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: false,
    didOpen: toast => {
        toast.addEventListener("mouseenter", Swal.stopTimer);
        toast.addEventListener("mouseleave", Swal.resumeTimer);
    }
});

import moment from "moment";
window.moment = moment;
Vue.filter("date", created => {
    return moment(created).format("DD.MM.YYYY, hh:mm:ss");
});
Vue.filter("crop_text", (text, length, clamp) => {
    clamp = clamp || "...";

    return text.length > length ? text.slice(0, length) + clamp : text;
});

window.toast = Toast;

import { Form, HasError, AlertError } from "vform";
window.Form = Form;

import Vue from "vue";

import { i18n } from "../plugins/i18n";

Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);

const app = new Vue({
    vuetify: Vuetify,
    router,
    store,
    i18n,
    // el: "#app",
    render: h => h(App)
}).$mount("#app");

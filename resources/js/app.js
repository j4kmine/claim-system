/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import VueRouter from 'vue-router'
import store from './store';
import routes from './routes.js';
import Toasted from 'vue-toasted';
import CoreuiVue from '@coreui/vue';
import { cilGarage, cilList, cilCalendar, cilNotes, cilBadge, cilNewspaper, cilCarAlt, cilMoney, cilLockLocked, cilUser, cilHome, cilAccountLogout, cilFolder, cilChartPie, cilPeople, cilDollar, cilPlus, cilMinus, cilAddressBook } from '@coreui/icons'
import DataTable from 'laravel-vue-datatable';
import helpers from './helpers';
import VueSignaturePad from 'vue-signature-pad';
import 'fullcalendar/dist/fullcalendar.css';
import FullCalendar from 'vue-full-calendar'
import VueHtml2pdf from 'vue-html2pdf'
const plugin = {
    install() {
        Vue.helpers = helpers
        Vue.prototype.$helpers = helpers
    }
}

window.Vue = require('vue');
Vue.use(plugin);
Vue.use(FullCalendar);
Vue.use(VueHtml2pdf)
Vue.use(DataTable);
Vue.use(VueRouter);
Vue.use(Toasted, { position: "bottom-center", duration: 1500 });
Vue.use(CoreuiVue);
Vue.use(VueSignaturePad);
Vue.filter('kb', val => {
    return Math.floor(val / 1024);
});
Vue.filter('toCurrency', function (value) {
    if (typeof value !== "number") {
        return value;
    }
    var formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0
    });
    return formatter.format(value);
});
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./', true, /\.vue$/i);
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

const VueUploadComponent = require('vue-upload-component');
Vue.component('file-upload', VueUploadComponent);


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
export const router = new VueRouter({
    mode: 'history',
    routes
});

const app = new Vue({
    el: '#app',
    store,
    router,
    icons: { cilGarage, cilList, cilCalendar, cilNotes, cilBadge, cilNewspaper, cilCarAlt, cilMoney, cilLockLocked, cilUser, cilHome, cilAccountLogout, cilFolder, cilChartPie, cilPeople, cilDollar, cilPlus, cilMinus, cilAddressBook }
});

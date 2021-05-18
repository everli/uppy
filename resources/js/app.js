/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import Vue from 'vue';
import {i18n, router, store} from './bootstrap';
import axios from 'axios';

window._ = require('lodash');
window.axios = axios;
window.Vue = require('vue').default;


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding views to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    router: router,
    store: store,
    i18n: i18n,
});

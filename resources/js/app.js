/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue').default;

import VueRouter from 'vue-router';
import VueAxios from 'vue-axios';
import Vuex from 'vuex';
import axios from 'axios';
import {routes} from './routes';
import VueCollapse from 'vue2-collapse'
import VueI18n from 'vue-i18n'
import englishLocale from './lang/en.json'

Vue.use(VueRouter);
Vue.use(Vuex)
Vue.use(VueAxios, axios);
Vue.use(VueCollapse)
Vue.use(VueI18n)

const router = new VueRouter({
    mode: 'history',
    routes: routes
});

const i18n = new VueI18n({
    locale: 'en',
    fallbackLocale: 'en',
    messages: {en: englishLocale}
});

const loadedLanguages = ['en'] // our default language that is preloaded

function setI18nLanguage(lang) {
    i18n.locale = lang;
    axios.defaults.headers.common['Accept-Language'] = lang;
    document.querySelector('html').setAttribute('lang', lang);
    return lang;
}

export function loadLanguageAsync(lang) {
    // If the same language
    if (i18n.locale === lang) {
        return Promise.resolve(setI18nLanguage(lang));
    }

    // If the language was already loaded
    if (loadedLanguages.includes(lang)) {
        return Promise.resolve(setI18nLanguage(lang));
    }

    // If the language hasn't been loaded yet
    return import(/* webpackChunkName: "lang/[request]" */ `./lang/${lang}.json`).then(
        messages => {
            i18n.setLocaleMessage(lang, messages.default);
            loadedLanguages.push(lang);
            return setI18nLanguage(lang);
        }
    ).catch(() => {
        console.log(`Locale "${lang}" not found, fallback used.`);
    })
}


const store = new Vuex.Store({
    state: {
        authenticated: false,
        config: window.config
    },
    mutations: {
        setAuthenticated(state, isAuthenticated) {
            state.authenticated = isAuthenticated;
        }
    },
    actions: {
        setAuthState({commit}, isAuthenticated = null) {
            let state = isAuthenticated !== null ? isAuthenticated : localStorage.getItem('authenticated') === 'true';
            localStorage.setItem('authenticated', state);
            commit('setAuthenticated', state);
        }
    }
})
store.dispatch('setAuthState');
export default store;

axios.interceptors.response.use((response) => response, (error) => {
    if (error.response.status === 401) {
        store.dispatch('setAuthState', false);
        router.go(0);
    }
    return Promise.reject(error);
});

router.beforeEach(function (to, from, next) {
    if (to.matched.some(record => record.meta.auth) && !store.state.authenticated) { // redirect to login, if not logged in
        next({name: 'login'});
    } else if (to.name === 'login' && store.state.authenticated) { // redirect to dashboard, if logged in
        next({name: 'application.index'});
    } else { // if not a protected route and not logged in, pass
        next();
    }
})

router.beforeEach((to, from, next) => {
    const lang = navigator.language.split('-')[0];
    loadLanguageAsync(lang).then(() => next());
})


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

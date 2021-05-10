import VueRouter from 'vue-router';
import {routes} from './routes';
import axios from 'axios';
import Vuex from 'vuex';
import VueI18n from 'vue-i18n';
import englishLocale from './lang/en.json';
import Vue from "vue";
import VueAxios from "vue-axios";
import {applicationsModule} from "./store/applicationsModule";

/**
 * Enable vue modules
 */

Vue.use(VueRouter);
Vue.use(Vuex)
Vue.use(VueAxios, axios);
Vue.use(VueI18n)

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

axios.defaults.headers.common['X-Requested-With'] = 'Uppy-SPA';
axios.defaults.withCredentials = true;

axios.interceptors.response.use((response) => response, (error) => {
    if (error.response.status === 401) {
        store.dispatch('setAuthState', false);
        router.go(0);
    }
    return Promise.reject(error);
});

/**
 * Vuex store configuration
 */
export const store = new Vuex.Store({
    state: {
        authenticated: false,
        config: window.config
    },
    mutations: {
        authenticated(state, isAuthenticated) {
            state.authenticated = isAuthenticated;
        }
    },
    actions: {
        setAuthState({commit}, isAuthenticated = null) {
            let state = isAuthenticated !== null ? isAuthenticated : localStorage.getItem('authenticated') === 'true';
            localStorage.setItem('authenticated', state);
            commit('authenticated', state);
        }
    },
    modules: {
        applications: applicationsModule
    }
})
store.dispatch('setAuthState');

/**
 * Vue-i18n configuration
 */
export const i18n = new VueI18n({
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

/**
 * Vue router configuration
 */

export const router = new VueRouter({
    mode: 'history',
    routes: routes
});

router.beforeEach(function (to, from, next) {
    if (to.matched.some(record => record.meta.auth) && !store.state.authenticated) { // redirect to login, if not logged in
        next({name: 'login'});
    } else if (to.name === 'login' && store.state.authenticated) { // redirect to dashboard, if logged in
        next({name: 'application.index'});
    } else { // if not a protected route and not logged in, pass
        next();
    }
});

router.beforeEach((to, from, next) => {
    const lang = navigator.language.split('-')[0];
    loadLanguageAsync(lang).then(() => next());
});

import Login from './views/auth/Login.vue';
import Home from './views/Home.vue';
import ApplicationIndex from './views/applications/ApplicationIndex';
import ApplicationCreate from './views/applications/ApplicationCreate';
import ApplicationBuilds from './views/builds/ApplicationBuilds';
import DownloadRedirect from './views/components/DownloadRedirect';
import NotFound from './views/errors/NotFound';
import Download from "./views/builds/Download";
import Upload from "./views/builds/Upload";
import Edit from "./views/builds/Edit";

export const routes = [
    {
        name: 'home',
        path: '/',
        component: Home,
        meta: {
            auth: false
        }
    },
    {
        name: 'login',
        path: '/login',
        component: Login,
        meta: {
            auth: false
        }
    },
    {
        name: 'application.index',
        path: '/applications',
        component: ApplicationIndex,
        meta: {
            auth: true
        }
    },
    {
        name: 'application.create',
        path: '/application/create',
        component: ApplicationCreate,
        meta: {
            auth: true
        }
    },
    {
        name: 'application.build.index',
        path: '/applications/:application/builds',
        component: ApplicationBuilds,
        meta: {
            auth: true
        }
    },
    {
        name: 'application.build.upload',
        path: '/applications/:application/builds/upload',
        component: Upload,
        meta: {
            auth: true
        }
    },
    {
        name: 'application.build.edit',
        path: '/applications/:application/builds/:build/edit',
        component: Edit,
        meta: {
            auth: true
        }
    },
    {
        name: 'application.redirect',
        path: '/:slug',
        component: DownloadRedirect,
        meta: {
            auth: false
        }
    },
    {
        name: 'application.download',
        path: '/applications/:slug/:platform',
        component: Download,
        meta: {
            auth: false
        }
    },
    {
        name: 'notFound',
        path: '/404',
        component: NotFound,
        meta: {
            auth: false
        }
    }
];

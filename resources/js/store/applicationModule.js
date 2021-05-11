export const applicationModule = {
    namespaced: true,
    state: () => ({
        applications: {},
        currentApp: null
    }),
    mutations: {
        currentApp(state, app) {
            state.currentApp = app;
        },
        applications(state, apps) {
            state.applications = apps;
        }
    },
    actions: {
        setCurrentAppById({commit, state}, id) {
            commit('currentApp', state.applications[id]);
        },
        async getApplications({commit}) {
            const response = await axios.get('/api/v1/applications');

            // map all applications in { app.id => app(obj) }
            const applications = response.data.reduce((all, app) => ({...all, [app.id]: app}), {});
            commit('applications', applications);
        },
        async getApplicationById({commit, state}, id) {
            const response = await axios.get(`/api/v1/applications/${id}`);

            commit('applications', {
                ...state.applications,
                ...{[id]: response.data.data}
            });
        }
    }
}

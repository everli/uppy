export const applicationsModule = {
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
        setCurrentApp({commit}, app) {
            commit('currentApp', app);
        },
        setApplications({commit}, applications) {
            // map all applications in { app.id => app(obj) }
            applications = applications.reduce((all, app) => ({...all, [app.id]: app}), {});
            commit('applications', applications);
        }
    },
    getters: {
        index: async (state) => {
            if (!_.isEmpty(state.applications)) {
                return state.applications;
            }

            const response = await axios.get('/api/v1/applications');
        }
    }
}
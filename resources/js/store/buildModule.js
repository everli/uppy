export const buildModule = {
    namespaced: true,
    state: () => ({
        builds: {},
    }),
    mutations: {
        builds(state, builds) {
            state.builds = builds
        }
    },
    actions: {
        async getBuildsByApplication({commit, state}, appId) {
            const response = await axios.get(`/api/v1/applications/${appId}/builds`);

            commit('builds', {
                ...state.builds,
                ...{[appId]: response.data.data}
            });
        }
    }
}

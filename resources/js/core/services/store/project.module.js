import srx from '../../../functions'
import router from '../../../router'

const state = {
    project: {},
}

const getters = {
    project: state => {
        return state.project
    },
}

const mutations = {
    SET_PROJECT: (state, payload) => {
        state.project = payload
    },
    RESET_DATA: (state) => {
        state.project = null
    },
}

const actions = {
    LOAD_PROJECT: async (context, payload) => {
        return axios.get('/api/projects/' + payload).then(
            response => {
                let project = response.data.project
                context.commit('SET_PROJECT', project)
                return response.data
            },
            (error) => {
                if (error.response.status === 422) {
                    srx.bootboxAlert(
                      `Проект с таким идентификатором {${payload}} не найден!`
                    );
                    setTimeout(() => {
                        router.push("/projects");
                    }, 500);
                }
            }
        )
    },
}

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions
}
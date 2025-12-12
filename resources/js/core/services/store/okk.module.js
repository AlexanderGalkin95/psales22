import _ from 'lodash'

const state = {
    project: null,
    settings: [],
    callTypes: [],
    heatTypes: [],
    criteria: [],
    additional_criteria: [],
    crm: [],
    objections: [],
    httpError: null,
}

const getters = {
    getProject: state => {
        return state.project
    },
    httpError: state => {
        return state.httpError
    },
    getCriteria: state => {
        return state.criteria
    },
    getAdditionalCriteria: state => {
        return state.additional_criteria
    },
    getCrm: state => {
        return state.crm
    },
    getObjections: state => {
        return state.objections
    },
    getCallTypes: state => {
        return state.callTypes
    },
    getHeatTypes: state => {
        return state.heatTypes
    },
    getSettings: state => {
        return state.settings
    },
}

const mutations = {
    SET_CALL_TYPES: (state, payload) => {
        state.callTypes = payload
    },
    SET_HEAT_TYPES: (state, payload) => {
        state.heatTypes = payload
    },
    SET_CRITERIA: (state, payload) => {
        state.criteria = payload
    },
    SET_ADDITIONAL_CRITERIA: (state, payload) => {
        state.additional_criteria = payload
        _.each(state.additional_criteria, item => {
            item.option_id = null
        })
    },
    SET_CRM: (state, payload) => {
        state.crm = payload
        _.each(state.crm, crm => {
            crm.value = null
        })
    },
    SET_OBJECTIONS: (state, payload) => {
        state.objections = payload ? payload.options : []
    },
    SET_SETTINGS: (state, payload) => {
        state.settings = payload
    },
    SET_PROJECT: (state, payload) => {
        state.project = payload
    },
    RESET_DATA: (state, payload) => {
        state.criteria = []
        state.additional_criteria = []
        state.crm = []
        state.objections = []
        state.project = null
        state.httpError = null
    },
}

const actions = {
    SEND_MESSAGE: (context, payload) => new Promise((resolve, reject) => {
        axios.post(`/api/projects/${payload.projectId}/call_ratings`, payload)
            .then(response => {
                resolve(response)
            }, error => {
                let errorMessage = ''
                if (error.response.status === 422) {
                    _.forEach(error.response.data.fields, field => {
                        errorMessage += `${field[0]}. `
                    })
                } else {
                    errorMessage = 'Произошла ошибка при попытке сохранить оценки звонка.'
                }
                error.response.data.message = errorMessage

                reject(error)
            })
    }),
    LOAD_CALL_TYPES: (context, payload) => new Promise((resolve, reject) => {
            axios.get(`/api/dictionaries/projects/${payload}/call_types`).then(response => {
                context.commit('SET_CALL_TYPES', response.data)
                resolve(response.data)
            })
    }),
    LOAD_HEAT_TYPES: (context) => new Promise((resolve, reject) => {
            axios.get(`/api/dictionaries/heat_types`).then(response => {
                context.commit('SET_HEAT_TYPES', response.data.heat_types)
                resolve(response.data.heat_types)
            })
    }),
    LOAD_PROJECT: (context, payload) => new Promise(resolve => {
        return axios.get('/api/projects/' + payload).then(response => {
            let project = response.data.project
            context.commit('SET_PROJECT', project)
            _.each(project.criteria, criteria => {
                criteria.value = null
                criteria.disabled = true
            })
            context.commit('SET_CRITERIA', project.criteria)
            context.commit('SET_ADDITIONAL_CRITERIA', project.additional_criteria)
            context.commit('SET_CRM', project.crm)
            context.commit('SET_OBJECTIONS', project.objections)
            resolve(project)
        })
    }),
    LOAD_SETTINGS: (context, payload) => new Promise((resolve, reject) => {
        return axios.get(`/api/projects/${payload}/settings`).then(response => {
            context.commit('SET_SETTINGS', response.data.settings)
            resolve(response.data.settings)
        })
    })
}

export default {
    state,
    getters,
    mutations,
    actions
}
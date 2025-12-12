import Vue from "vue";
import Vuex from "vuex";
import JwtService from "../jwt.service";
import {PURGE_AUTH, SET_AUTH, SET_ERROR, SET_PASSWORD} from "./auth.module";

Vue.use(Vuex);

export const SET_USER = "setUser";

const mutations = {
    [SET_USER](state, user) {
        state.user = user;
    },
};

export default new Vuex.Store({
  state: {},
  mutations,
  actions: {},
  getters: {},
});

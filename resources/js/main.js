import Vue from "vue";
import VueResource from 'vue-resource';
import moment from 'moment'

Vue.use(VueResource);

Vue.http.interceptors.push((request, next) => {
    request.headers.set('Cache-Control', 'no-cache');
    if(window.Laravel){
        request.headers.set('X-CSRF-TOKEN', window.Laravel.csrfToken);
    }
    next();
});

Vue.http.interceptors.push(() => {
    // return response callback
    return function(response) {
        if(response.status === 401){
            this.$store
                .dispatch(LOGOUT)
                .then(() => {
                    this.$http.post('/logout')
                        .then(response => {
                            window.location.href = "/login"
                        });

                });
        }
    };
});

import App from "./views/App";
import router from "./router";
import store from "./core/services/store";

Vue.config.productionTip = false;

Vue.prototype.$eventBus = Vue.prototype.$eventBus || new Vue

// Global 3rd party plugins
import "popper.js";
import "tooltip.js";
import PerfectScrollbar from "perfect-scrollbar";
window.PerfectScrollbar = PerfectScrollbar;
import ClipboardJS from "clipboard";
window.ClipboardJS = ClipboardJS;

// Vue 3rd party plugins
import i18n from "./core/plugins/vue-i18n";
import "./core/plugins/portal-vue";
import "./core/plugins/bootstrap-vue";
import "./core/plugins/perfect-scrollbar";
import "./core/plugins/inline-svg";
import "./core/plugins/apexcharts";
import "./core/plugins/metronic";
import "./core/plugins/treeselect";
import "@mdi/font/css/materialdesignicons.css";
import ToastPlugin from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-bootstrap.css';


import vSelect from "@artamas/vue-select";
import {LOGOUT} from "./core/services/store/auth.module";
Vue.component("v-select", vSelect);
Vue.filter('dateFilter', (date, format) => {
    return moment(date).format(format)
})
Vue.use(ToastPlugin);

new Vue({
  router,
  store,
  i18n,
  render: h => h(App)
}).$mount("#app");

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue').default;

import VueResource from 'vue-resource';
import Vue from "vue";
import draggableComponent from "vuedraggable";
Vue.use(VueResource);
Vue.component('draggable', draggableComponent);

Vue.http.interceptors.push((request, next) => {
    request.headers.set('Cache-Control', 'no-cache');
    if(window.Laravel){
        request.headers.set('X-CSRF-TOKEN', window.Laravel.csrfToken);
    }
    next();
});

/**
 * We'll register a HTTP interceptor to attach the "CSRF" header to each of
 * the outgoing requests issued by this application. The CSRF middleware
 * included with Laravel will automatically verify the header's value.
 */



/*
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import "./main";
$(document).on('click', 'a[href="#"]',(e)=>{
    e.preventDefault();
})


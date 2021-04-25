import Vue from "vue";

require('./bootstrap');


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import Main from './components/Main.vue'

Vue.component('tournaments', Main);

new Vue({
    el: '#app',
    components: {Main}
});

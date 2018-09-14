window.bootstrap = require('bootstrap');
window.axios = require('axios');

import Vue from 'vue';
import VueI18n from 'vue-i18n';
import VueRouter from 'vue-router';
import BootstrapVue from 'bootstrap-vue'
import VueProgressBar from 'vue-progressbar'
import Notifications from 'vue-notification'

window.Vue = Vue;

import router from './utils/router'
import messages from "./utils/messages";
import './utils/filters'

Vue.use(VueRouter);
Vue.use(BootstrapVue);
Vue.use(Notifications);
Vue.use(VueProgressBar, {
    thickness: '4px'
});

const i18n = new VueI18n({
    locale: 'en',
    messages
});

Vue.component('home-component', require('./components/HomeComponent.vue'));
Vue.component('tvshow-component', require('./components/TvShowComponent.vue'));
Vue.component('media-popover-component', require('./components/MediaPopoverComponent'));
Vue.component('media-component', require('./components/MediaComponent'));
Vue.component('status-popover-component', require('./components/StatusPopoverComponent'));
Vue.component('library-stats-component', require('./components/LibraryStatsComponent'));

export default new Vue({router, i18n}).$mount('#app');

require('./utils/axios');
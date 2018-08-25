import VueRouter from "vue-router";

const movies = require('../components/MoviesComponent.vue');
const tvShows = require('../components/TvShowsComponent');

var routes = [
    {
        path: '/',
        redirect: 'movies'
    },
    {
        path: '/movies',
        component: movies
    },
    {
        path: '/tvshows',
        component: tvShows
    }
];

if (!window.location.pathname.startsWith('/home')) {
    routes = [];
}

const router = new VueRouter({routes});
export default router;
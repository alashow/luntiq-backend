import VueRouter from "vue-router";

const movies = require('../components/MoviesComponent.vue');
const tvShows = require('../components/TvShowsComponent');
const movie = require('../components/MovieComponent');
const episode = require('../components/EpisodeComponent');

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
    },
    {
        path: '/movie/:id',
        component: movie,
        props: true
    },
    {
        path: '/episode/:id',
        component: episode,
        props: true
    }
];

if (!window.location.pathname.startsWith('/home')) {
    routes = [];
}

const router = new VueRouter({routes});
export default router;
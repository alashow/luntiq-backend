import Vue from "vue"

Vue.filter("tmdbImage", (path, size) => {
    if (size === undefined) {
        size = 'w185';
    }
    return `https://image.tmdb.org/t/p/${size}//${path}`;
});
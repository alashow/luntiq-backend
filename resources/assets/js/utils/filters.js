import Vue from "vue"

Vue.filter("tmdbImage", (path, size) => {
    if (size === undefined) {
        size = 'w185';
    }
    return `https://image.tmdb.org/t/p/${size}//${path}`;
});

Vue.filter("bytes", (bytes) => humanFileSize(bytes));
Vue.filter("bytesSpeed", (bytes) => humanFileSize(bytes) + '/s');

function humanFileSize(bytes, si) {
    var thresh = si ? 1000 : 1024;
    if (Math.abs(bytes) < thresh) {
        return bytes + ' B';
    }
    var units = si ? ['kB', 'MB', 'GB'] : ['KiB', 'MiB', 'GiB'];
    var u = -1;
    do {
        bytes /= thresh;
        ++u;
    } while (Math.abs(bytes) >= thresh && u < units.length - 1);
    return bytes.toFixed(1) + ' ' + units[u];
}
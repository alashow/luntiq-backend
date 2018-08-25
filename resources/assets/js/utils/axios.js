import app from '../app';
import notify from './notify'

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

axios.interceptors.request.use(config => {
    app.$Progress.start();
    return config;
}, function (error) {
    console.log('request.fail', error);
    app.$Progress.fail();
    return Promise.reject(error);
});

axios.interceptors.response.use(response => {
    app.$Progress.finish();
    return response;
}, function (error) {
    console.log('response.fail', error);
    app.$Progress.fail();
    if (error.response) {
        notify.error({
            title: app.$t('errors.network.title'),
            text: app.$t('errors.network.message') + ': ' + error.response.status
        });

        if (error.response.status === 403 || error.response.status === 401) {
            location.reload();
        }
    }
    if (!error.status) {
        notify.error({
            title: app.$t('errors.network.title'),
            text: app.$t('errors.network.message')
        });
    }
    return Promise.reject(error);
});
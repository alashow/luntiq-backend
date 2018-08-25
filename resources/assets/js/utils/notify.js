import Vue from "vue"

const error = function (options) {
    Vue.notify({
        type: 'error',
        ...options
    })
};

export default {
    error
}
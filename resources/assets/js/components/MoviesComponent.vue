<template>
    <div class="py-4">
        <div>
            <b-button variant="primary" class="mr-1 mt-1" :disabled="loading" @click="load">
                <span class="fa fa-sync" v-bind:class="{ 'fa-spin': loading }"/>
                {{ $t('refresh') }}
            </b-button>

            <b-button variant="primary" class="mr-1 mt-1" :disabled="loading" @click="toggleAll">
                {{ toggleAllValue ? $t('movie.enableAll') : $t('movie.disableAll') }}
            </b-button>

            <library-stats-component class="mt-1"></library-stats-component>
        </div>

        <b-table striped hover responsive class="mt-3"
                 v-if="movies.length > 0" :items="movies" :fields="fields">
            <template slot="title" slot-scope="data">
                <media-popover-component
                        prefix="movie"
                        :id="data.item.id"
                        :label="data.item.title"
                        :overview="data.item.overview"
                        :poster="data.item.poster_path">
                </media-popover-component>
            </template>

            <template slot="actions" slot-scope="data">
                <div class="row">
                    <status-popover-component class="mr-1 mt-1" :file="data.item.file.id"/>
                    <router-link :to="'/movie/'+data.item.id">
                        <b-button size="sm" variant="outline-primary" class="mt-1">
                            <span class="fa play"/>
                            {{ $t('actions.play') }}
                        </b-button>
                    </router-link>
                </div>
            </template>

            <template slot="download" slot-scope="data">
                <b-form-checkbox v-model="data.item.download" @change="toggleDownload($event, data.item.id)">
                </b-form-checkbox>
            </template>
        </b-table>

        <b-alert show variant="danger" class="mt-3"
                 v-if="!loading && movies.length === 0">
            {{ $t('movie.empty') }}
        </b-alert>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                loading: false,
                toggleAllValue: false,

                movies: [],
                fields: [
                    {
                        key: 'title',
                        label: this.$i18n.t('movie.title'),
                        sortable: true
                    },
                    {
                        key: 'actions',
                        label: this.$i18n.t('actions.title'),
                    },
                    {
                        key: 'download',
                        label: this.$i18n.t('movie.download'),
                        sortable: true
                    }
                ],
            }
        },
        mounted() {
            this.load();
        },
        methods: {
            findMovie(movieId) {
                return this.movies.find(movie => movie.id === movieId)
            },
            load() {
                this.loading = true;
                axios.get(route('movies'))
                    .then(function (response) {
                        this.loading = false;
                        this.movies = response.data.data.movies;
                    }.bind(this))
                    .catch(function (error) {
                        this.loading = false;
                    }.bind(this));
            },
            update(movieId, params) {
                let movie = this.findMovie(movieId);
                axios.patch(route('movie.update', {movie: movieId}), params)
                    .then(function (response) {
                        movie = response.data.data.movie;
                    }.bind(this))
                    .catch(function (error) {
                        movie.download = !movie.download;
                    }.bind(this));
            },
            toggleDownload(download, movieId) {
                this.update(movieId, {download: download});
            },
            toggleAll() {
                const params = {download: this.toggleAllValue};

                axios.post(route('movie.toggleAll'), params)
                    .then(function (response) {
                        this.load();
                        this.toggleAllValue = !response.data.data.enabled;
                    }.bind(this));
            }
        }
    }
</script>

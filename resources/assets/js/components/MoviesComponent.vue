<template>
    <div class="py-4">
        <b-button size="medium" variant="primary" class="mb-4"
                  :disable="loading"
                  @click="load">
            <span class="fa fa-sync" v-bind:class="{ 'fa-spin': loading }"/>
            {{ $t('refresh') }}
        </b-button>

        <b-button size="medium" variant="primary" class="mb-4"
                  :disable="loading"
                  @click="toggleAll">
            {{ toggleAllValue ? $t('movie.enableAll') : $t('movie.disableAll') }}
        </b-button>

        <b-table striped hover
                 v-if="movies.length > 0"
                 :items="movies"
                 :fields="fields">
            <template slot="title" slot-scope="data">
                <media-popover-component
                        prefix="movie"
                        :id="data.item.id"
                        :label="data.item.title"
                        :overview="data.item.overview"
                        :poster="data.item.poster_path">
                </media-popover-component>
            </template>

            <template slot="download" slot-scope="data">
                <b-form-checkbox v-model="data.item.download"
                                 @change="toggleDownload($event, data.item.id)">
                </b-form-checkbox>
            </template>
        </b-table>

        <b-alert show variant="danger"
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

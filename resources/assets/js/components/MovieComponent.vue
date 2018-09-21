<template>
    <div class="py-4">
        <div v-if="movie">
            <h1>
                <media-popover-component
                        prefix="movie"
                        :id="movie.id"
                        :label="movie.title"
                        :overview="movie.overview"
                        :poster="movie.poster_path">
                </media-popover-component>
            </h1>
            <player-component v-if="movie.file"
                              :url="movie.file.stream_link || movie.file.link"
                              :poster="movie.backdrop_path || movie.poster_path">
            </player-component>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['id'],
        data() {
            return {
                movie: {},
            }
        },
        mounted() {
            this.load();
        },
        methods: {
            load() {
                axios.get(route('movie', {movie: this.id}))
                    .then(function (response) {
                        this.movie = response.data.data.movie;
                    }.bind(this));
            },
        }
    }
</script>

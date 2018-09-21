<template>
    <div class="py-4">
        <div v-if="episode">
            <h1>
                <media-popover-component
                        prefix="episode"
                        :id="episode.id"
                        :label="episode.name || episode.season.name || episode.show.name"
                        :overview="episode.name.overview || episode.season.overview || episode.show.overview"
                        :poster="poster">
                </media-popover-component>
            </h1>
            <player-component v-if="episode.file"
                              :url="episode.file.stream_link || episode.file.link"
                              :poster="backdrop || poster">
            </player-component>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['id'],
        data() {
            return {
                episode: undefined,
            }
        },
        computed: {
            poster() {
                return (this.episode.poster_path || this.episode.season.poster_path || this.episode.show.poster_path)
            },
            backdrop() {
                return (this.episode.backdrop_path || this.episode.season.backdrop_path || this.episode.show.backdrop_path)
            },
        },
        mounted() {
            this.load();
        },
        methods: {
            load() {
                axios.get(route('episode', {episode: this.id}))
                    .then(function (response) {
                        this.episode = response.data.data.episode;
                    }.bind(this));
            },
        }
    }
</script>

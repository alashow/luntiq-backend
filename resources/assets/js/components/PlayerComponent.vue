<template>
    <div class="py-4">
        <div v-if="url">
            <video-player
                    class="video-player-box"
                    ref="videoPlayer"
                    :options="playerOptions"
                    :playsinline="true"/>
        </div>
    </div>
</template>

<script>
    import {videoPlayer} from 'vue-video-player'

    export default {
        components: {videoPlayer},
        props: ['url', 'poster'],
        data() {
            return {
                playerOptions: {},
            }
        },
        mounted() {
            this.playerOptions = {
                autoplay: true,
                muted: false,
                fluid: true,
                captions: true,
                language: 'en',
                playbackRates: [0.7, 1.0, 1.5, 2.0],
                sources: [{
                    src: this.url,
                }],
                poster: this.$options.filters.tmdbImage(this.poster, 'original'),
            };
        }
    }
</script>

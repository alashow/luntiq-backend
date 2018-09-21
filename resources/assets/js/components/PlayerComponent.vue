<template>
    <div class="py-4">
        <div v-if="url">
            <video-player
                    class="video-player-box"
                    ref="videoPlayer"
                    :options="playerOptions"
                    :playsinline="true"
                    @ready="onReady"/>
        </div>
    </div>
</template>

<script>
    import {videoPlayer} from 'vue-video-player'
    import 'videojs-hotkeys'

    export default {
        components: {videoPlayer},
        props: ['url', 'poster'],
        data() {
            return {
                playerOptions: {},
            }
        },
        computed: {
            player() {
                return this.$refs.videoPlayer.player
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
        },
        methods: {
            onReady(player){
                player.hotkeys({
                    volumeStep: 0.1,
                    seekStep: 5,
                    enableModifiersForNumbers: false
                });
            }
        }
    }
</script>

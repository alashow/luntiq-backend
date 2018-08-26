<template>
    <div class="py-4">
        <div class="py-4">
            <media-component
                    :label="show.name"
                    :overview="show.overview"
                    :poster="show.poster_path">
            </media-component>
        </div>

        <b-button-toolbar>
            <div class="my-1">
                <b-btn variant="primary" :disabled="loading" @click="refresh">
                    <span class="fa fa-sync" v-bind:class="{ 'fa-spin': loading }"/>
                    {{ $t('refresh') }}
                </b-btn>

                <b-btn size="medium" variant="primary" :disabled="loading" @click="toggleDownload(true)">
                    {{ toggleAllValue ? $t('show.enableAll') : $t('show.disableAll') }}
                </b-btn>

                <b-btn size="medium" variant="primary" :disabled="loading">
                    <b-form-checkbox v-model="show.download" @change="toggleDownload(false)">
                        {{ $t('show.download') }}
                    </b-form-checkbox>
                </b-btn>
            </div>
            <b-button-group class="ml-sm-1 my-1">
                <b-btn v-b-toggle="'episodes'+show.id" size="medium" variant="success">
                    {{ $t('show.episodes') }}
                </b-btn>
            </b-button-group>
        </b-button-toolbar>

        <b-collapse :id="'episodes'+show.id">
            <b-table striped hover responsive
                     class="mt-4"
                     v-if="show"
                     :items="show.episodes"
                     :fields="fields">
                <template slot="name" slot-scope="data">
                    <media-popover-component
                            prefix="episode"
                            :id="data.item.id"
                            :label="data.item.name"
                            :overview="data.item.overview"
                            :poster="data.item.poster_path || show.poster_path">
                    </media-popover-component>
                </template>

                <template slot="status" slot-scope="data">
                    <status-popover-component :data="data.item.status"/>
                </template>

                <template slot="download" slot-scope="data">
                    <b-form-checkbox v-model="data.item.download" @change="changeEpisode($event, data.item.id)">
                    </b-form-checkbox>
                </template>
            </b-table>
        </b-collapse>
    </div>
</template>

<script>
    export default {
        props: ['tvshow'],
        data() {
            return {
                loading: false,
                toggleAllValue: !this.tvshow.download,
                show: this.tvshow,
                fields: [
                    {
                        key: 'name',
                        label: this.$i18n.t('episode.name'),
                        sortable: true
                    },
                    {
                        key: 'season',
                        label: this.$i18n.t('episode.season'),
                        sortable: true
                    },
                    {
                        key: 'episode',
                        label: this.$i18n.t('episode.episode'),
                        sortable: true
                    },
                    {
                        key: 'status',
                        label: this.$i18n.t('status.title'),
                        sortable: true
                    },
                    {
                        key: 'download',
                        label: this.$i18n.t('episode.download'),
                        sortable: true
                    }
                ],
            }
        },
        watch: {
            tvshow: function () {
                this.show = this.tvshow;
            }
        },
        methods: {
            refresh() {
                this.loading = true;
                axios.get(route('show', {show: this.show.id}))
                    .then(function (response) {
                        this.loading = false;
                        this.show = response.data.data.show;
                    }.bind(this))
                    .catch(function (error) {
                        this.loading = false;
                    }.bind(this));
            },
            updateShow(params) {
                axios.patch(route('show.update', {show: this.show.id}), params)
                    .then(function (response) {
                        this.show = response.data.data.show;
                    }.bind(this));
            },
            toggleDownload(recursive) {
                const params = {download: this.toggleAllValue, recursive: recursive};

                axios.post(route('show.toggleDownload', {show: this.show.id}), params)
                    .then(function (response) {
                        this.refresh();
                        this.toggleAllValue = !response.data.data.enabled;
                    }.bind(this));
            },

            findEpisode(episodeId) {
                return this.show.episodes.find(episode => episode.id === episodeId)
            },
            updateEpisode(episodeId, params) {
                let episode = this.findEpisode(episodeId);
                axios.patch(route('episode.update', {episode: episodeId}), params)
                    .then(function (response) {
                        episode = response.data.data.episode;
                    }.bind(this))
                    .catch(function (error) {
                        episode = !episode.download;
                    }.bind(this));
            },
            changeEpisode(download, episodeId) {
                this.updateEpisode(episodeId, {download: download});
            },
        }
    }
</script>
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

                <b-btn variant="primary" :disabled="loading" @click="toggleDownload(true)">
                    {{ toggleAllValue ? $t('show.enableAll') : $t('show.disableAll') }}
                </b-btn>

                <b-btn variant="primary" :disabled="loading">
                    <b-form-checkbox v-model="show.download" @change="toggleDownload(false)">
                        {{ $t('show.download') }}
                    </b-form-checkbox>
                </b-btn>
            </div>
            <b-button-group class="ml-sm-1 my-1">
                <b-btn v-b-toggle="'seasons'+show.id" variant="success">
                    {{ $t('show.seasons') }}
                </b-btn>
            </b-button-group>
        </b-button-toolbar>

        <b-collapse :id="'seasons'+show.id" v-if="show">
            <b-list-group v-for="season in show.seasons" :key="season.id" class="mt-3">
                <div>
                    <b-list-group-item button v-b-toggle="'season'+season.id">
                        {{ season.name }}
                    </b-list-group-item>
                    <b-collapse :id="'season'+season.id">

                        <div class="py-3">
                            <b-btn variant="primary" :disabled="season.loading" @click="refreshSeason(season.id)">
                                <span class="fa fa-sync" v-bind:class="{ 'fa-spin': season.loading }"></span>
                                {{ $t('refresh') }}
                            </b-btn>

                            <b-btn variant="primary" :disabled="season.loading" @click="toggleSeason(season.id)">
                                {{ season.toggle ? $t('season.enableAll') : $t('season.disableAll')
                                }}
                            </b-btn>
                        </div>

                        <b-table striped hover responsive
                                 :items="season.episodes"
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
                                <status-popover-component :file="data.item.file.id"/>
                            </template>

                            <template slot="download" slot-scope="data">
                                <b-form-checkbox v-model="data.item.download" :indeterminate.sync="data.item.download == null" @change="changeEpisode($event, data.item.id)">
                                </b-form-checkbox>
                            </template>
                        </b-table>
                    </b-collapse>
                </div>
            </b-list-group>
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
                        this.show = response.data.data.show;
                    }.bind(this))
                    .then(function () {
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

            findSeason(seasonId) {
                return this.show.seasons.find(season => season.id === seasonId)
            },
            refreshSeason(seasonId) {
                let season = this.findSeason(seasonId);
                season.loading = true;
                axios.get(route('season', {season: seasonId}))
                    .then(function (response) {
                        Object.assign(season, response.data.data.season);
                    }.bind(this))
                    .then(function () {
                        let season = this.findSeason(seasonId);
                        season.loading = false;
                    }.bind(this));
            },
            toggleSeason(seasonId) {
                let season = this.findSeason(seasonId);
                const params = {download: season.toggle};

                axios.post(route('season.toggleDownload', {season: seasonId}), params)
                    .then(function (response) {
                        this.refreshSeason(seasonId);
                        season.toggle = !response.data.data.enabled;
                    }.bind(this));
            },

            findEpisode(episodeId) {
                return this.show.seasons.map(o => o.episodes).find(episode => episode.id === episodeId)
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
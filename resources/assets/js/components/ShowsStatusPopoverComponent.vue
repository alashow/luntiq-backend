<template>
    <b-btn variant="outline-success" @click="check" :id="targetId">
        {{ $t('status.shows.check') }}
        <b-popover :target="targetId"
                   :title="$t('status.title')"
                   :disabled="!status.episode_count"
                   triggers="hover focus"
                   ref="popover">
            <ul class="list-unstyled" v-if="status.episode_count">
                <li>{{ $t('status.shows.episode_count') }}: {{ status.episode_count }}</li>
                <li>{{ $t('status.shows.scanned') }}: {{ status.scanned }}</li>
                <li>{{ $t('status.shows.unchecked') }}: {{ status.unchecked }}</li>
                <li>{{ $t('status.shows.dead') }}: {{ status.dead }}</li>
                <li>{{ $t('status.shows.complete') }}: {{ status.complete }}</li>
                <li>{{ $t('status.shows.active') }}: {{ status.active }}</li>
                <li>{{ $t('status.shows.waiting') }}: {{ status.waiting }}</li>

                <li class="dropdown-divider"></li>
                <li>{{ $t('status.shows.size.total') }}: {{ status.size.total | bytes }}</li>
                <li>{{ $t('status.shows.size.downloaded') }}: {{ status.size.downloaded | bytes }}</li>
                <li>{{ $t('status.shows.size.downloading') }}: {{ status.size.downloading | bytes }}</li>
                <li>{{ $t('status.speed') }}: {{ status.speed | bytesSpeed }}</li>
            </ul>

            <b-form-checkbox v-model="autoRefresh" @change="toggleAutoRefresh">
                {{ $t('autoRefresh') }}
            </b-form-checkbox>
        </b-popover>
    </b-btn>
</template>

<script>
    export default {
        props: ['all', 'show', 'season'],
        data() {
            return {
                status: {},
                targetId: 'shows_status_' + this.season + this.show + this.all,
                autoRefresh: false,
                refreshIntervalId: -1,
            }
        },
        methods: {
            check() {
                const params = {
                    all: this.all,
                    show: this.show,
                    season: this.season
                };
                axios.get(route('downloads.shows'), {params: params})
                    .then(function (response) {
                        this.status = response.data.data.status;
                        setTimeout(() => this.$refs.popover.$emit('open'), 50);
                    }.bind(this))
            },
            toggleAutoRefresh(enable) {
                window.clearInterval(this.refreshIntervalId);
                if (enable) {
                    this.refreshIntervalId = setInterval(() => this.check(), 1000);
                }
            }
        }
    }
</script>

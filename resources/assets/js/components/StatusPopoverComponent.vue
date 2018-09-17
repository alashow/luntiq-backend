<template>
    <div>
        <b-btn size="sm" variant="outline-primary" @click="check" :id="targetId">
            {{ $t('status.check') }}
        </b-btn>
        <b-popover :target="targetId"
                   :title="$t('status.title')"
                   :disabled="!status.status"
                   triggers="hover focus"
                   ref="popover">
            <ul class="list-unstyled">
                <li>{{ $t('status.state') }}: {{ status.status }}</li>
                <li class="dropdown-divider"></li>
                <li>{{ $t('status.total') }}: {{ status.totalLength | bytes }}</li>
                <li>{{ $t('status.done') }}: {{ status.completedLength | bytes }}</li>
                <li v-if="status.downloadSpeed">
                    {{ $t('status.speed') }}: {{ status.downloadSpeed | bytesSpeed }}
                </li>
                <li v-if="status.connections">
                    {{ $t('status.connections') }}: {{ status.connections }}
                </li>

                <li v-if="status.progress">
                    <status-progress-component :status="status"></status-progress-component>
                </li>
            </ul>

            <b-form-checkbox v-model="autoRefresh" @change="toggleAutoRefresh">
                {{ $t('autoRefresh') }}
            </b-form-checkbox>
        </b-popover>
    </div>
</template>

<script>
    import StatusProgressComponent from "./StatusProgressComponent";

    export default {
        components: {StatusProgressComponent},
        props: ['file'],
        data() {
            return {
                status: {},
                targetId: 'file_' + this.file,
                autoRefresh: false,
                refreshIntervalId: -1
            }
        },
        methods: {
            check() {
                axios.get(route('downloads.check', {file: this.file}))
                    .then(function (response) {
                        this.status = response.data.data.status;
                        this.$refs.popover.$emit('open');
                        this.stopAutoRefreshIfComplete();
                    }.bind(this))
            },
            toggleAutoRefresh(enable) {
                window.clearInterval(this.refreshIntervalId);
                if (enable) {
                    this.refreshIntervalId = setInterval(() => this.check(), 1000);
                }
            },
            stopAutoRefreshIfComplete() {
                if (this.status.status === 'complete') {
                    this.autoRefresh = false;
                    this.toggleAutoRefresh(false)
                }
            }
        },
    }
</script>

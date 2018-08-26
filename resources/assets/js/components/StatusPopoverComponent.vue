<template>
    <div>
        <div v-if="status">
            <span :id="status.gid">{{ status.status }}</span>
            <b-popover :target="status.gid"
                       triggers="hover focus"
                       :title="$t('status.title')">
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
                </ul>
            </b-popover>
        </div>
        <div v-if="!status">
            {{ $t('status.none') }}
        </div>
    </div>
</template>

<script>
    export default {
        props: ['data'],
        data() {
            return {
                status: this.data,
                timeoutId: 0,
            }
        },
        watch: {
            data: function () {
                this.status = this.data;
                this.start();
            }
        },
        mounted() {
            this.start();
        },
        methods: {
            start() {
                if (this.status) {
                    this.check();
                }
            },
            check() {
                if (!this.status) {
                    return;
                }
                axios.get(route('downloads.check', {id: this.status.gid}))
                    .then(function (response) {
                        this.status = response.data.data.status;

                        let delay = this.getCheckDelay();
                        if (delay > 0) {
                            clearTimeout(this.timeoutId);
                            this.timeoutId = setTimeout(() => this.check(), delay);
                        }
                    }.bind(this))
            },
            getCheckDelay() {
                if (this.status) {
                    switch (this.status.status) {
                        case 'active':
                            return 5000;
                        case 'waiting':
                            return 15000;
                        case 'paused':
                            return 30000;
                        default:
                            return 0;
                    }
                } else {
                    return 0;
                }
            }
        }
    }
</script>

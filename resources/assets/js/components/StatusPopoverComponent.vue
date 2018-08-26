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
            </ul>
        </b-popover>
    </div>
</template>

<script>
    export default {
        props: ['file'],
        data() {
            return {
                status: {},
                targetId: 'file_' + this.file
            }
        },
        methods: {
            check() {
                axios.get(route('downloads.check', {file: this.file}))
                    .then(function (response) {
                        this.status = response.data.data.status;
                        this.$refs.popover.$emit('open');
                    }.bind(this))
            },
        }
    }
</script>

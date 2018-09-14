<template>
    <b-btn variant="outline-success" @click="check" :id="targetId">
        {{ $t('libraryStats.label') }}
        <b-popover :target="targetId"
                   :title="$t('libraryStats.label')"
                   :disabled="!library.size"
                   triggers="hover focus"
                   ref="popover">

            <div v-for="folder in library" :key="folder.folder" class="my-2">
                <h4>{{ folder.folder }}</h4>
                <ul class="list-unstyled">
                    <li>{{ $t('libraryStats.count') }}: {{ folder.count }}</li>
                    <li>{{ $t('libraryStats.size') }}: {{ folder.size | bytes }}</li>
                    <li>{{ $t('libraryStats.average') }}: {{ folder.average | bytes }}</li>
                </ul>
            </div>
        </b-popover>
    </b-btn>
</template>

<script>
    export default {
        data() {
            return {
                library: [],
                targetId: 'libraryStats'
            }
        },
        methods: {
            check() {
                axios.get(route('downloads.library'))
                    .then(function (response) {
                        this.library = response.data.data.library;
                        setTimeout(() => this.$refs.popover.$emit('open'), 50);
                    }.bind(this))
            }
        }
    }
</script>

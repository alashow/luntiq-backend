<template>
    <div class="py-4">
        <div>
            <b-btn size="medium" variant="primary" class="mr-1 mt-1" :disabled="loading" @click="load" cl>
                <span class="fa fa-sync" v-bind:class="{ 'fa-spin': loading }"/>
                {{ $t('shows.refresh') }}
            </b-btn>

            <b-btn size="medium" variant="primary" class="mr-1 mt-1" :disabled="loading" @click="clearAll">
                <span class="fa fa-trash-alt"/>
                {{ $t('shows.clearAll') }}
            </b-btn>

            <shows-status-popover-component class="mt-1" :all="true"></shows-status-popover-component>
        </div>

        <tvshow-component class="mt-3" v-for="show in shows" :key="show.id" :tvshow="show"></tvshow-component>

        <b-alert show variant="danger" class="mt-3"
                 v-if="!loading && shows.length === 0">
            {{ $t('shows.empty') }}
        </b-alert>
    </div>
</template>

<script>
    import ShowsStatusPopoverComponent from "./ShowsStatusPopoverComponent";

    export default {
        components: {ShowsStatusPopoverComponent},
        data() {
            return {
                loading: false,
                shows: [],
            };
        },
        mounted() {
            this.load();
        },
        methods: {
            load() {
                this.loading = true;
                axios.get(route('shows'))
                    .then(function (response) {
                        this.loading = false;
                        this.shows = response.data.data.shows;
                    }.bind(this))
                    .catch(function (error) {
                        this.loading = false;
                    }.bind(this));
            },
            clearAll() {
                axios.get(route('shows.clearAll'))
                    .then(function (response) {
                        this.load();
                    }.bind(this));
            }
        }
    }
</script>

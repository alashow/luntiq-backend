<template>
    <div>
        <div class="pt-4">
            <b-btn size="medium" variant="primary"
                   :disabled="loading"
                   @click="load">
                <span class="fa fa-sync" v-bind:class="{ 'fa-spin': loading }"/>
                {{ $t('shows.refresh') }}
            </b-btn>

            <b-btn size="medium" variant="primary"
                   :disabled="loading"
                   @click="clearAll">
                <span class="fa fa-trash-alt"/>
                {{ $t('shows.clearAll') }}
            </b-btn>
        </div>

        <tvshow-component v-for="show in shows"
                          :key="show.id"
                          :tvshow="show">
        </tvshow-component>
    </div>
</template>

<script>
    export default {
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

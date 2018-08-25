<template>
    <div>
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
                        console.log(error);
                    });
            }
        }
    }
</script>

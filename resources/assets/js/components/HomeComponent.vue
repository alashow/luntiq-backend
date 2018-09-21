<template>
    <div>
        <b-nav fill tabs>
            <b-nav-item to="/movies">{{ $t('movies') }}</b-nav-item>
            <b-nav-item to="/tvshows">{{ $t('tvShows') }}</b-nav-item>
        </b-nav>

        <router-view></router-view>
    </div>
</template>

<script>
    export default {
    }
</script>

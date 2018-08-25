<template>
    <div>
        <b-media no-body>
            <b-media-aside vertical-align="center">
                <b-img :src="poster | tmdbImage('w342')" :alt="label" width="154" height="231">
                </b-img>
            </b-media-aside>
            <b-media-body class="ml-3">
                <h4 class="mt-0">{{ label }}</h4>
                <p>
                    {{ overview }}
                </p>
            </b-media-body>
        </b-media>
    </div>
</template>

<script>
    export default {
        props: ['label', 'overview', 'poster']
    }
</script>

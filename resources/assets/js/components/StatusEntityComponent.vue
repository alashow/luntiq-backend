<template>
    <div>
        <b-progress :value="status.progress" :variant="variant" :animated="active" show-progress class="mt-2"></b-progress>
    </div>
</template>

<script>
    export default {
        props: ['entity'],
        data(){

        },
        watch: {
            entity: function () {
                this.show = this.tvshow;
            }
        },
    }
</script>

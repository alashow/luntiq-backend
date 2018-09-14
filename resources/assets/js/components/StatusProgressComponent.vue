<template>
    <div>
        <b-progress :value="status.progress" :variant="variant" :animated="active" show-progress class="mt-2"></b-progress>
    </div>
</template>

<script>
    export default {
        props: ['status'],
        computed: {
            active() {
                return this.status.status === 'active';
            },
            variant() {
                if (this.active) {
                    return 'primary';
                } else if (this.status.progress >= 100) {
                    return 'success'
                } else {
                    return 'danger';
                }
            },
        }
    }
</script>

<template>
    <div>
        <span :id="prefix + id">
                    {{ label }}
                </span>
        <b-popover :target="prefix + id"
                   triggers="hover focus">
            <media-component
                    :label="label"
                    :overview="overview"
                    :poster="poster">
            </media-component>
        </b-popover>
    </div>
</template>

<script>
    export default {
        props: ['prefix', 'id', 'label', 'overview', 'poster']
    }
</script>

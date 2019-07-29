<template>
    <div></div>
</template>

<script>
    export default {
        name: 'Heading',

        render: function (createElement) {
            return createElement(
                'h' + this.level,   // tag name
                this.$slots.default // array of children
            )
        },

        props: {
            level: {
              type: Number,
              required: true
            }
        }
    };
</script>
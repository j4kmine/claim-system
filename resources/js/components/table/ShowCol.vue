<template>
    <span class="btn btn-link p-0" @click="click(data)">
        Show
    </span>
</template>

<script>
export default {
    props: {
        data: {},
        click: {
            type: Function,
            default: () => {}
        }
    }
}
</script>
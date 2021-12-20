<template>
    <div v-if="data.items[0] != null">
        {{ data.items[0].item }}
    </div>
</template>

<script>
export default {
    props: {
        data: {}
    }
}
</script>
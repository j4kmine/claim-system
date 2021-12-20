<template>
    <div v-if="data.appointments[0] != null">
        {{ data.appointments[0].formatted_time }}
    </div>
</template>

<script>
export default {
    props: {
        data: {}
    }
}
</script>
<template>
    <div v-if="data.appointments[0] != null">
        {{ data.appointments[0].formatted_date }}
    </div>
</template>

<script>
export default {
    props: {
        data: {}
    }
}
</script>
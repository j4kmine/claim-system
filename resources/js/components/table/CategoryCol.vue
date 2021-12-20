<template>
    <div style="text-transform: capitalize;">
        {{ $helpers.unslugify(this.data.category) }}
    </div>
</template>

<script>
export default {
    props: {
        data: {}
    }
}
</script>
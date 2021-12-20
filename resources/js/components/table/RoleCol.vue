<template>
    <div style="text-transform: capitalize;">
        {{ $helpers.unslugify(this.data.role) }}
    </div>
</template>

<script>
export default {
    props: {
        data: {}
    }
}
</script>
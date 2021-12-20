<template>
    <div>
        {{ this.data.granted_by ? "Owner" : "User" }}
    </div>
</template>

<script>
export default {
    props: {
        data: {}
    }
}
</script>
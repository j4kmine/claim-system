<template>
  <div>
    <span
      :style="{ backgroundColor: data.service_type.color }"
      style="border-radius: 5px; padding: 2px"
    >
      {{ data.service_type.name }}
    </span>
  </div>
</template>

<script>
export default {
  props: {
    data: {},
  },
};
</script>
